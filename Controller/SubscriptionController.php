<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractFormController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MauticPlugin\LodgeSubscriptionBundle\Form\Type\PaymentType;

class SubscriptionController extends AbstractFormController
{
    public function indexAction($page = 1)
    {
        $subscriptionModel = $this->getModel('lodge.subscription');
        
        $limit = $this->get('mautic.helper.core_parameters')->get('default_pagelimit');
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);

        $rates = $subscriptionModel->getSubscriptionRates($start, $limit);
        
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

    public function newRateAction(Request $request)
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
            $this->getModel('lodge.subscription')->saveRate($year, $amount);
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function processPaymentAction(Request $request)
    {
        $contactId = $request->request->get('contactId');
        $amount = $request->request->get('amount');
        
        try {
            $stripeService = $this->get('mautic.lodge.service.stripe');
            $contact = $this->getModel('lead')->getEntity($contactId);
            
            if (!$contact) {
                throw new \Exception('Contact not found');
            }

            $session = $stripeService->createCheckoutSession(
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
    public function paymentFormAction($contactId)
    {
        $contact = $this->getModel('lead')->getEntity($contactId);
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
                    
                    // Get the subscription helper service
                    $subscriptionHelper = $this->get('mautic.lodge.helper.subscription');
                    
                    try {
                        // Record the payment
                        $payment = $subscriptionHelper->recordPayment(
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
                $stripeService = $this->get('mautic.lodge.service.stripe');
                $session = $stripeService->createCheckoutSession(
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
    public function recordPaymentAction(Request $request)
    {
        $contactId = $request->request->get('contactId');
        $amount = $request->request->get('amount');
        $paymentMethod = $request->request->get('paymentMethod', 'manual');
        $notes = $request->request->get('notes');
        
        if (!$contactId || !$amount) {
            return new JsonResponse(['success' => false, 'message' => 'Contact ID and amount are required']);
        }
        
        try {
            $subscriptionHelper = $this->get('mautic.lodge.helper.subscription');
            
            // Record the payment
            $payment = $subscriptionHelper->recordPayment(
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
    public function generatePaymentLinkAction(Request $request)
    {
        $contactId = $request->request->get('contactId');
        $amount = $request->request->get('amount');
        
        if (!$contactId || !$amount) {
            return new JsonResponse(['success' => false, 'message' => 'Contact ID and amount are required']);
        }

        try {
            $contact = $this->getModel('lead')->getEntity($contactId);
            if (!$contact) {
                return new JsonResponse(['success' => false, 'message' => 'Contact not found']);
            }

            $email = $contact->getEmail();
            if (!$email) {
                return new JsonResponse(['success' => false, 'message' => 'Contact has no email address']);
            }

            $stripeService = $this->get('mautic.lodge.service.stripe');
            $session = $stripeService->createCheckoutSession($contactId, $amount, $email);

            return new JsonResponse([
                'success' => true,
                'paymentUrl' => $session->url
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}