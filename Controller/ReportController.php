<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractFormController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel;

class ReportController extends AbstractFormController
{
    private $leadModel;
    private $entityManager;
    private $subscriptionModel;
    
    /**
     * Constructor
     */
    public function __construct(
        LeadModel $leadModel, 
        EntityManagerInterface $entityManager,
        SubscriptionModel $subscriptionModel
    ) {
        $this->leadModel = $leadModel;
        $this->entityManager = $entityManager;
        $this->subscriptionModel = $subscriptionModel;
    }
    
    /**
     * Display subscription statistics dashboard
     */
    public function dashboardAction(Request $request, $year = null): Response
    {
        if (!$year) {
            $year = date('Y');
        }

        // Get statistics using the injected model
        $stats = $this->subscriptionModel->getSubscriptionStatusSummary($year);
        
        // Get payment statistics
        $paymentRepo = $this->entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        $paymentStats = $paymentRepo->getPaymentStatistics($year);
        
        // Get available years for dropdown
        $rates = $this->entityManager
            ->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate')
            ->getAllRates();
        
        $years = [];
        foreach ($rates as $rate) {
            $years[] = $rate->getYear();
        }
        
        // Add current year if not in the list
        if (!in_array(date('Y'), $years)) {
            $years[] = date('Y');
        }
        
        // Sort years
        rsort($years);
        
        return $this->delegateView([
            'viewParameters' => [
                'stats' => $stats,
                'paymentStats' => $paymentStats,
                'year' => $year,
                'years' => $years,
                'permissions' => [
                    'view' => $this->security->isGranted('lodge:subscriptions:view'),
                ]
            ],
            'contentTemplate' => 'LodgeSubscriptionBundle:Report:dashboard.html.php',
            'pagetitle' => 'Subscription Dashboard'
        ]);
    }

    /**
     * Export payments report
     */
    public function exportAction(Request $request): Response
    {
        $year = $request->query->get('year', date('Y'));
        
        // Get payment repository
        $paymentRepo = $this->entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        
        // Get all payments for the year
        $startDate = new \DateTime($year . '-01-01');
        $endDate = new \DateTime($year . '-12-31 23:59:59');
        
        $payments = $paymentRepo->getPaymentsInDateRange($startDate, $endDate);
        
        // Prepare CSV data
        $csv = "Date,Contact ID,Contact Name,Amount,Method,Status,Applied to Current,Applied to Arrears,Notes\n";
        
        foreach ($payments as $payment) {
            $contactId = $payment->getContactId();
            $contactName = '';
            
            // Get contact name
            $contact = $this->leadModel->getEntity($contactId);
            if ($contact) {
                $contactName = $contact->getName();
            }
            
            $date = $payment->getDateAdded()->format('Y-m-d H:i:s');
            $amount = number_format($payment->getAmount(), 2);
            $method = $payment->getPaymentMethod();
            $status = $payment->getStatus();
            $appliedToCurrent = number_format($payment->getAppliedToCurrent(), 2);
            $appliedToArrears = number_format($payment->getAppliedToArrears(), 2);
            $notes = str_replace('"', '""', $payment->getNotes() ?? '');
            
            $csv .= "{$date},{$contactId},\"{$contactName}\",{$amount},{$method},{$status},{$appliedToCurrent},{$appliedToArrears},\"{$notes}\"\n";
        }
        
        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="lodge_payments_' . $year . '.csv"');
        
        return $response;
    }

    public function annualReportAction(Request $request)
    {
        // ... existing code ...
        
        $paymentRepo = $this->entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        
        // ... existing code ...
        
        $rateRepo = $this->entityManager
            ->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate')
            ->getRateForYear($year);
        
        // ... existing code ...
    }
    
    public function contactReportAction(Request $request, $contactId)
    {
        // ... existing code ...
        
        $paymentRepo = $this->entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        
        // ... existing code ...
    }
} 