<?php
namespace MauticPlugin\LodgeSubscriptionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Mautic\EmailBundle\Model\EmailModel;

class ConfigType extends AbstractType
{
    private $emailModel;

    public function __construct(EmailModel $emailModel)
    {
        $this->emailModel = $emailModel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $emailTemplateChoices = $this->getEmailTemplateChoices();

        $builder->add(
            'lodge_subscription_currency',
            ChoiceType::class,
            [
                'label' => 'Currency',
                'choices' => [
                    'GBP (£)' => 'GBP',
                    'USD ($)' => 'USD',
                    'EUR (€)' => 'EUR',
                    'CAD ($)' => 'CAD',
                    'AUD ($)' => 'AUD',
                ],
                'expanded' => false,
                'multiple' => false,
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => true,
            ]
        );

        $builder->add(
            'lodge_subscription_reminder_template',
            ChoiceType::class,
            [
                'label' => 'Reminder Email Template',
                'choices' => $emailTemplateChoices,
                'expanded' => false,
                'multiple' => false,
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
                'placeholder' => 'Select email template...'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lodgesubscriptionconfig';
    }

    /**
     * Get email template choices for dropdown
     */
    private function getEmailTemplateChoices()
    {
        $choices = [];
        $emailTemplates = $this->emailModel->getRepository()->getEmailList('', 0, 9999, 0, '', 'name');

        foreach ($emailTemplates as $template) {
            $choices[$template['name']] = $template['id'];
        }

        return $choices;
    }
}