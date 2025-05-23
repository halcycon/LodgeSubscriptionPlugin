<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;

class ReportController
{
    protected EntityManagerInterface $entityManager;
    protected LeadModel $leadModel;
    protected CorePermissions $security;
    protected SubscriptionModel $subscriptionModel;
    protected RouterInterface $router;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        LeadModel $leadModel,
        CorePermissions $security,
        SubscriptionModel $subscriptionModel,
        RouterInterface $router
    ) {
        $this->entityManager = $entityManager;
        $this->leadModel = $leadModel;
        $this->security = $security;
        $this->subscriptionModel = $subscriptionModel;
        $this->router = $router;
    }

    /**
     * Display subscription statistics dashboard (HTML view)
     */
    public function dashboardAction(Request $request, $year = null): Response
    {
        if (!$year) {
            $year = (int) date('Y');
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
        if (!in_array((int) date('Y'), $years)) {
            $years[] = (int) date('Y');
        }
        
        // Sort years
        rsort($years);
        
        // Return HTML template response for main dashboard
        $viewParameters = [
            'stats' => $stats,
            'paymentStats' => $paymentStats,
            'year' => $year,
            'years' => $years,
            'permissions' => [
                'view' => $this->security->isGranted('lodge:subscriptions:view'),
            ]
        ];
        
        // For now, return JSON until we have a proper view renderer
        return new JsonResponse($viewParameters);
    }

    /**
     * Dashboard API endpoint (JSON response)
     */
    public function dashboardApiAction(Request $request, $year = null): Response
    {
        if (!$year) {
            $year = (int) date('Y');
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
                'view' => $this->security->isGranted('lodge:subscriptions:view'),
            ]
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

    public function annualReport(Request $request): Response
    {
        $year = $request->query->get('year', date('Y'));
        
        $paymentRepo = $this->entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        $paymentStats = $paymentRepo->getPaymentStatistics($year);
        
        $rateRepo = $this->entityManager
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
        $paymentRepo = $this->entityManager->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
        $payments = $paymentRepo->getContactPayments($contactId);
        
        $content = json_encode([
            'contactId' => $contactId,
            'payments' => $payments
        ]);
        
        return new Response($content, 200, ['Content-Type' => 'application/json']);
    }
} 