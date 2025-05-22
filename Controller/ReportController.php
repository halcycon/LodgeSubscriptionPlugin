<?php
namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractFormController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReportController extends AbstractFormController
{
    /**
     * Display subscription statistics dashboard
     */
    public function dashboardAction($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        // Get the subscription model
        $subscriptionModel = $this->get('mautic.lodge.model.subscription');
        
        // Get statistics
        $stats = $subscriptionModel->getSubscriptionStatusSummary($year);
        
        // Get payment statistics
        $paymentRepo = $this->getDoctrine()->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        $paymentStats = $paymentRepo->getPaymentStatistics($year);
        
        // Get available years for dropdown
        $rates = $this->getDoctrine()
            ->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate')
            ->getAllRates();
        
        $years = [];
        foreach ($rates as $rate) {
            $years[] = $rate->getYear();
        }
        
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
            'contentTemplate' => 'LodgeSubscriptionPlugin:Report:dashboard.html.php',
            'pagetitle' => 'Subscription Dashboard'
        ]);
    }

    /**
     * Export payments report
     */
    public function exportAction(Request $request)
    {
        $year = $request->query->get('year', date('Y'));
        
        // Get payment repository
        $paymentRepo = $this->getDoctrine()->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        
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
            $contact = $this->getModel('lead')->getEntity($contactId);
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
        
        $response = new \Symfony\Component\HttpFoundation\Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="lodge_payments_' . $year . '.csv"');
        
        return $response;
    }

    public function annualReportAction(Request $request)
    {
        // ... existing code ...
        
        $paymentRepo = $this->getDoctrine()->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        
        // ... existing code ...
        
        $rateRepo = $this->getDoctrine()
            ->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate')
            ->getRateForYear($year);
        
        // ... existing code ...
    }
    
    public function contactReportAction(Request $request, $contactId)
    {
        // ... existing code ...
        
        $paymentRepo = $this->getDoctrine()->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        
        // ... existing code ...
    }
} 