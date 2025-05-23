<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Mautic\LeadBundle\Model\FieldModel;
use Mautic\EmailBundle\Model\EmailModel;
use Doctrine\ORM\EntityManager;
use MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper;
use MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate;

class YearEndProcessCommand extends Command
{
    private $subscriptionHelper;
    private $fieldModel;
    private $emailModel;
    private $entityManager;

    public function __construct(
        SubscriptionHelper $subscriptionHelper,
        FieldModel $fieldModel,
        EmailModel $emailModel,
        EntityManager $entityManager
    ) {
        parent::__construct();
        $this->subscriptionHelper = $subscriptionHelper;
        $this->fieldModel = $fieldModel;
        $this->emailModel = $emailModel;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setName('lodge:subscription:yearend')
            ->setDescription('Process year-end subscription tasks')
            ->addOption(
                'year',
                null,
                InputOption::VALUE_REQUIRED,
                'Current year being processed'
            )
            ->addOption(
                'email-template',
                null,
                InputOption::VALUE_OPTIONAL,
                'ID of email template for reminders'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Run without making changes'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currentYear = $input->getOption('year') ?? date('Y');
        $nextYear = $currentYear + 1;
        $emailTemplateId = $input->getOption('email-template');
        $isDryRun = $input->getOption('dry-run');

        $output->writeln("<info>Starting year-end processing for {$currentYear}</info>");

        try {
            // Verify next year's subscription rate exists
            $nextYearRate = $this->entityManager
                ->getRepository(SubscriptionRate::class)
                ->getRateForYear($nextYear);

            if (!$nextYearRate) {
                throw new \Exception("No subscription rate defined for year {$nextYear}. Please set the rate first.");
            }

            $output->writeln("Next year's subscription rate: £" . number_format($nextYearRate->getAmount(), 2));

            // 1. Create new fields for next year if they don't exist
            $this->createNextYearFields($nextYear, $output, $isDryRun);

            // 2. Process existing contacts
            $this->processContacts($currentYear, $nextYear, $nextYearRate->getAmount(), $output, $isDryRun);

            // 3. Send reminders if email template is specified
            if ($emailTemplateId) {
                $this->sendReminders($emailTemplateId, $output, $isDryRun);
            }

            $output->writeln("<info>Year-end processing completed successfully</info>");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln("<e>Error: " . $e->getMessage() . "</e>");
            return Command::FAILURE;
        }
    }

    private function createNextYearFields($nextYear, OutputInterface $output, $isDryRun)
    {
        $fields = [
            [
                'label' => "Craft {$nextYear} Due",
                'alias' => "craft_{$nextYear}_due",
                'type' => 'boolean',
                'isPublished' => true,
                'group' => 'lodge_subscriptions'
            ],
            [
                'label' => "Craft {$nextYear} Paid",
                'alias' => "craft_{$nextYear}_paid",
                'type' => 'boolean',
                'isPublished' => true,
                'group' => 'lodge_subscriptions'
            ]
        ];

        foreach ($fields as $field) {
            if (!$this->fieldModel->getEntityByAlias($field['alias'])) {
                $output->writeln("Creating field: " . $field['alias']);
                if (!$isDryRun) {
                    $this->fieldModel->createCustomField($field);
                }
            } else {
                $output->writeln("Field already exists: " . $field['alias']);
            }
        }
    }

    private function processContacts($currentYear, $nextYear, $newRate, OutputInterface $output, $isDryRun)
    {
        $contacts = $this->entityManager->getRepository(\Mautic\LeadBundle\Entity\Lead::class)->findAll();
        $processed = 0;
        $errors = 0;

        foreach ($contacts as $contact) {
            try {
                $output->writeln("\nProcessing contact: " . $contact->getName());

                // Get current values
                $currentOwed = (float)$contact->getFieldValue('craft_owed_current');
                $currentArrears = (float)$contact->getFieldValue('craft_owed_arrears');
                $isDue = (bool)$contact->getFieldValue("craft_{$currentYear}_due");

                $output->writeln("  Current owed: £" . number_format($currentOwed, 2));
                $output->writeln("  Current arrears: £" . number_format($currentArrears, 2));

                if (!$isDryRun) {
                    // Move unpaid current year dues to arrears
                    if ($currentOwed > 0) {
                        $newArrears = $currentArrears + $currentOwed;
                        $contact->addUpdatedField('craft_owed_arrears', $newArrears);
                        $output->writeln("  Moving £" . number_format($currentOwed, 2) . " to arrears");
                    }

                    // Copy due status to next year if applicable
                    if ($isDue) {
                        $contact->addUpdatedField("craft_{$nextYear}_due", true);
                        $output->writeln("  Setting due status for {$nextYear}");
                    }

                    // Reset current year fields
                    $contact->addUpdatedField('craft_paid_current', false);
                    $contact->addUpdatedField('craft_owed_current', $newRate);
                    $contact->addUpdatedField("craft_{$nextYear}_paid", false);

                    $output->writeln("  Setting new subscription amount: £" . number_format($newRate, 2));

                    $this->entityManager->persist($contact);
                }

                $processed++;

            } catch (\Exception $e) {
                $output->writeln("<e>Error processing contact {$contact->getId()}: " . $e->getMessage() . "</e>");
                $errors++;
            }
        }

        if (!$isDryRun) {
            $this->entityManager->flush();
        }

        $output->writeln("\nProcessing summary:");
        $output->writeln("  Contacts processed: " . $processed);
        $output->writeln("  Errors encountered: " . $errors);
    }

    private function sendReminders($emailTemplateId, OutputInterface $output, $isDryRun)
    {
        $email = $this->emailModel->getEntity($emailTemplateId);
        if (!$email) {
            throw new \Exception("Email template ID {$emailTemplateId} not found");
        }

        $contacts = $this->entityManager->getRepository(\Mautic\LeadBundle\Entity\Lead::class)
            ->findBy(['craft_paid_current' => false]);

        $output->writeln("\nSending subscription reminders:");
        $sent = 0;
        $errors = 0;

        foreach ($contacts as $contact) {
            if (!$contact->getEmail()) {
                $output->writeln("  Skipping contact {$contact->getId()} - no email address");
                continue;
            }

            $output->writeln("  Sending reminder to: " . $contact->getEmail());

            if (!$isDryRun) {
                try {
                    $this->emailModel->sendEmail($email, [
                        'lead' => $contact,
                        'email' => $contact->getEmail(),
                    ]);
                    $sent++;
                } catch (\Exception $e) {
                    $output->writeln("<e>Error sending email to {$contact->getEmail()}: " . $e->getMessage() . "</e>");
                    $errors++;
                }
            } else {
                $sent++;
            }
        }

        $output->writeln("  Reminders sent: " . $sent);
        $output->writeln("  Reminders sent: " . $sent . " of " . count($contacts));
    }
}