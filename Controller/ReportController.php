<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReportController extends CommonController
{
    /**
     * Display subscription statistics dashboard (HTML view)
     */
    public function dashboardAction(Request $request, $year = null): Response
    {
        if (!$year) {
            $year = (int) date('Y');
        }

        // Get services from container
        $subscriptionModel = $this->get('mautic.lodge.model.subscription');
        $entityManager = $this->getDoctrine()->getManager();
        $security = $this->get('mautic.security');

        // Get statistics using the injected model
        $stats = $subscriptionModel->getSubscriptionStatusSummary($year);
        
        // Get payment statistics
        $paymentRepo = $entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        $paymentStats = $paymentRepo->getPaymentStatistics($year);
        
        // Get available years for dropdown
        $rates = $entityManager
            ->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate')
            ->getAllRates();
        
        $years = [];
        foreach ($rates as $rate) {
            $years[] = $rate->getYear();
        }
        
        // Add current year if not in the list
        if (!in_array((int) date('Y'), $years)) {
            $years[] = (int) date('Y');
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
                    'view' => $security->isGranted('lodge:subscriptions:view'),
                ]
            ],
            'contentTemplate' => 'LodgeSubscriptionBundle:Report:dashboard.html.php',
            'passthroughVars' => [
                'activeLink' => '#mautic_subscription_dashboard',
                'mauticContent' => 'lodge_subscription_dashboard',
                'route' => $this->generateUrl('mautic_subscription_dashboard', ['year' => $year])
            ]
        ]);
    }

    /**
     * Dashboard API endpoint (JSON response)
     */
    public function dashboardApiAction(Request $request, $year = null): Response
    {
        if (!$year) {
            $year = (int) date('Y');
        }

        // Get services from container
        $subscriptionModel = $this->get('mautic.lodge.model.subscription');
        $entityManager = $this->getDoctrine()->getManager();
        $security = $this->get('mautic.security');

        // Get statistics using the injected model
        $stats = $subscriptionModel->getSubscriptionStatusSummary($year);
        
        // Get payment statistics
        $paymentRepo = $entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        $paymentStats = $paymentRepo->getPaymentStatistics($year);
        
        // Get available years for dropdown
        $rates = $entityManager
            ->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate')
            ->getAllRates();
        
        $years = [];
        foreach ($rates as $rate) {
            $years[] = $rate->getYear();
        }
        
        // Add current year if not in the list
        if (!in_array((int) date('Y'), $years)) {
            $years[] = (int) date('Y');
        }
        
        // Sort years
        rsort($years);
        
        // Return JSON response for API
        return new JsonResponse([
            'stats' => $stats,
            'paymentStats' => $paymentStats,
            'year' => $year,
            'years' => $years,
            'permissions' => [
                'view' => $security->isGranted('lodge:subscriptions:view'),
            ]
        ]);
    }

    /**
     * Export payments report
     */
    public function exportAction(Request $request): Response
    {
        $year = $request->query->get('year', date('Y'));
        
        // Get services from container
        $entityManager = $this->getDoctrine()->getManager();
        $leadModel = $this->getModel('lead');
        
        // Get payment repository
        $paymentRepo = $entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        
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
            $contact = $leadModel->getEntity($contactId);
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

    public function annualReport(Request $request): Response
    {
        $year = $request->query->get('year', date('Y'));
        
        $entityManager = $this->getDoctrine()->getManager();
        $paymentRepo = $entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        $paymentStats = $paymentRepo->getPaymentStatistics($year);
        
        $rateRepo = $entityManager
            ->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate')
            ->getRateForYear($year);
        
        $content = json_encode([
            'paymentStats' => $paymentStats,
            'rate' => $rateRepo,
            'year' => $year
        ]);
        
        return new Response($content, 200, ['Content-Type' => 'application/json']);
    }
    
    public function contactReport(Request $request, $contactId): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $paymentRepo = $entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        $payments = $paymentRepo->getContactPayments($contactId);
        
        $content = json_encode([
            'contactId' => $contactId,
            'payments' => $payments
        ]);
        
        return new Response($content, 200, ['Content-Type' => 'application/json']);
    }
} 