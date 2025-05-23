<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractFormController;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\LodgeSubscriptionBundle\Form\Type\PaymentType;
use MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper;
use MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel;
use MauticPlugin\LodgeSubscriptionBundle\Services\StripeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends AbstractFormController
{
    protected LeadModel $leadModel;
    protected CoreParametersHelper $coreParametersHelper;
    protected SubscriptionModel $subscriptionModel;
    protected SubscriptionHelper $subscriptionHelper;
    protected StripeService $stripeService;
    
    public function __construct(
        LeadModel $leadModel,
        CoreParametersHelper $coreParametersHelper,
        SubscriptionModel $subscriptionModel,
        SubscriptionHelper $subscriptionHelper,
        StripeService $stripeService
    ) {
        $this->leadModel = $leadModel;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->subscriptionModel = $subscriptionModel;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->stripeService = $stripeService;
    }
    
    public function indexAction($page = 1): Response
    {
        $limit = $this->coreParametersHelper->get('default_pagelimit');
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);

        $rates = $this->subscriptionModel->getSubscriptionRates($start, $limit);
        
        return $this->delegateView([
            'viewParameters' => [
                'rates' => $rates,
                'page' => $page,
                'limit' => $limit,
                'totalRates' => count($rates)
            ],
            'contentTemplate' => 'LodgeSubscriptionBundle:SubscriptionRate:list.html.php',
            'pagetitle' => 'Subscription Rates'
        ]);
    }

    public function newRateAction(Request $request): JsonResponse
    {
        $year = $request->request->get('year');
        $amount = $request->request->get('amount');

        if (!$year || !$amount) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Year and amount are required'
            ]);
        }

        try {
            $this->subscriptionModel->saveRate($year, $amount);
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function processPaymentAction(Request $request): JsonResponse
    {
        $contactId = $request->request->get('contactId');
        $amount = $request->request->get('amount');
        
        try {
            $contact = $this->leadModel->getEntity($contactId);
            
            if (!$contact) {
                throw new \Exception('Contact not found');
            }

            $session = $this->stripeService->createCheckoutSession(
                $contactId,
                $amount,
                $contact->getEmail()
            );

            return new JsonResponse([
                'success' => true,
                'sessionUrl' => $session->url
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display payment form
     */
    public function paymentFormAction($contactId): Response
    {
        $contact = $this->leadModel->getEntity($contactId);
        if (!$contact) {
            return $this->notFound();
        }

        $currentOwed = (float)$contact->getFieldValue('craft_owed_current');
        $arrearsOwed = (float)$contact->getFieldValue('craft_owed_arrears');
        $totalOwed = $currentOwed + $arrearsOwed;
        $currentYear = date('Y');

        // Create the payment form
        $form = $this->createForm(
            PaymentType::class, 
            [
                'amount' => $totalOwed,
                'contactId' => $contactId,
                'year' => $currentYear,
                'currentOwed' => $currentOwed,
                'arrearsOwed' => $arrearsOwed
            ]
        );

        // Check if form is submitted
        if ($this->request->isMethod('POST')) {
            if (!$this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    $formData = $form->getData();
                    
                    try {
                        // Record the payment
                        $payment = $this->subscriptionHelper->recordPayment(
                            $contactId,
                            $formData['amount'],
                            $formData['year'],
                            $formData['paymentMethod'],
                            null,
                            'completed'
                        );
                        
                        $this->addFlash(
                            'mautic.core.notice.created',
                            [
                                '%name%' => 'Payment',
                                '%menu_link%' => 'mautic_dashboard_index',
                                '%url%' => $this->generateUrl('mautic_contact_action', 
                                    ['objectAction' => 'view', 'objectId' => $contactId]
                                ),
                            ]
                        );
                        
                        return $this->redirectToRoute('mautic_contact_action', [
                            'objectAction' => 'view', 
                            'objectId' => $contactId
                        ]);
                    } catch (\Exception $e) {
                        $this->addFlash(
                            'mautic.core.error.payment',
                            ['%message%' => $e->getMessage()]
                        );
                    }
                }
            } else {
                return $this->redirectToRoute('mautic_contact_action', [
                    'objectAction' => 'view', 
                    'objectId' => $contactId
                ]);
            }
        }

        // Generate Stripe payment link if integration is enabled
        $stripePaymentLink = null;
        if ($totalOwed > 0) {
            try {
                $session = $this->stripeService->createCheckoutSession(
                    $contactId,
                    $totalOwed,
                    $contact->getEmail()
                );
                $stripePaymentLink = $session->url;
            } catch (\Exception $e) {
                // Silent fail - Stripe might not be configured
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                    'contact' => $contact,
                    'currentOwed' => $currentOwed,
                    'arrearsOwed' => $arrearsOwed,
                    'totalOwed' => $totalOwed,
                    'stripePaymentLink' => $stripePaymentLink,
                ],
                'contentTemplate' => 'LodgeSubscriptionBundle:Subscription:payment_form.html.php',
                'passthroughVars' => [
                    'mauticContent' => 'subscriptionPayment',
                    'route' => $this->generateUrl(
                        'mautic_subscription_payment_form', 
                        ['contactId' => $contactId]
                    )
                ],
            ]
        );
    }

    /**
     * Record a manual payment
     */
    public function recordPaymentAction(Request $request): JsonResponse
    {
        $contactId = $request->request->get('contactId');
        $amount = $request->request->get('amount');
        $paymentMethod = $request->request->get('paymentMethod', 'manual');
        $notes = $request->request->get('notes');
        
        if (!$contactId || !$amount) {
            return new JsonResponse(['success' => false, 'message' => 'Contact ID and amount are required']);
        }
        
        try {
            // Record the payment
            $payment = $this->subscriptionHelper->recordPayment(
                $contactId,
                $amount,
                date('Y'),
                $paymentMethod,
                null,
                'completed',
                $notes
            );
            
            return new JsonResponse([
                'success' => true, 
                'message' => 'Payment recorded successfully',
                'paymentId' => $payment->getId()
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Generate a payment link
     */
    public function generatePaymentLinkAction(Request $request): JsonResponse
    {
        $contactId = $request->request->get('contactId');
        $amount = $request->request->get('amount');
        
        if (!$contactId || !$amount) {
            return new JsonResponse(['success' => false, 'message' => 'Contact ID and amount are required']);
        }

        try {
            $contact = $this->leadModel->getEntity($contactId);
            if (!$contact) {
                return new JsonResponse(['success' => false, 'message' => 'Contact not found']);
            }

            $email = $contact->getEmail();
            if (!$email) {
                return new JsonResponse(['success' => false, 'message' => 'Contact has no email address']);
            }

            $session = $this->stripeService->createCheckoutSession($contactId, $amount, $email);

            return new JsonResponse([
                'success' => true,
                'paymentUrl' => $session->url
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}