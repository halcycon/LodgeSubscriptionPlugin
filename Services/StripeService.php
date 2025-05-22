<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Services;

use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\LodgeSubscriptionPlugin\Helper\SubscriptionHelper;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\RouterInterface;

class StripeService
{
    private $integrationHelper;
    private $router;
    private $subscriptionHelper;
    private $integration;

    public function __construct(
        IntegrationHelper $integrationHelper,
        RouterInterface $router,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->integrationHelper = $integrationHelper;
        $this->router = $router;
        $this->subscriptionHelper = $subscriptionHelper;
        
        $this->integration = $this->integrationHelper->getIntegrationObject('LodgeSubscription');
        
        if ($this->integration && $this->integration->getIntegrationSettings()->getIsPublished()) {
            $keys = $this->integration->getDecryptedApiKeys();
            Stripe::setApiKey($keys['stripe_secret_key']);
        }
    }

    public function createCheckoutSession($contactId, $amount, $email)
    {
        if (!$this->integration || !$this->integration->getIntegrationSettings()->getIsPublished()) {
            throw new \Exception('Stripe integration is not configured or enabled');
        }

        $successUrl = $this->router->generate(
            'mautic_page_public',
            [
                'alias' => 'lodge-payment-success',
                'contact' => $contactId
            ],
            RouterInterface::ABSOLUTE_URL
        );

        $cancelUrl = $this->router->generate(
            'mautic_page_public',
            [
                'alias' => 'lodge-payment-cancel',
                'contact' => $contactId
            ],
            RouterInterface::ABSOLUTE_URL
        );

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'gbp',
                        'unit_amount' => (int)($amount * 100),
                        'product_data' => [
                            'name' => 'Lodge Subscription',
                            'description' => 'Annual Lodge Subscription Payment'
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_email' => $email,
                'metadata' => [
                    'contact_id' => $contactId,
                    'amount' => $amount,
                    'year' => date('Y')
                ]
            ]);

            // Create a pending payment record
            $this->subscriptionHelper->recordPayment(
                $contactId,
                $amount,
                date('Y'),
                'stripe',
                null, // Stripe payment ID will be updated when payment completes
                'pending'
            );

            return $session;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Stripe checkout session: ' . $e->getMessage());
        }
    }

    public function handleWebhook($payload, $sigHeader)
    {
        if (!$this->integration || !$this->integration->getIntegrationSettings()->getIsPublished()) {
            throw new \Exception('Stripe integration is not configured');
        }

        $keys = $this->integration->getDecryptedApiKeys();
        $webhookSecret = $keys['stripe_webhook_secret'];

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    
                    // Find pending payment and update it
                    $payment = $this->subscriptionHelper->recordPayment(
                        $session->metadata->contact_id,
                        $session->metadata->amount,
                        $session->metadata->year,
                        'stripe',
                        $session->payment_intent,
                        'completed'
                    );

                    // Update contact fields
                    $this->subscriptionHelper->updateContactPaymentStatus(
                        $session->metadata->contact_id,
                        $session->metadata->amount,
                        $session->metadata->year
                    );
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    
                    // Update payment record to failed status
                    $this->subscriptionHelper->updatePaymentStatus(
                        $paymentIntent->id,
                        'failed',
                        $paymentIntent->last_payment_error ? $paymentIntent->last_payment_error->message : null
                    );
                    break;

                case 'charge.refunded':
                    $charge = $event->data->object;
                    
                    // Handle refund if needed
                    $this->subscriptionHelper->handleRefund(
                        $charge->payment_intent,
                        $charge->amount_refunded / 100 // Convert from cents to pounds
                    );
                    break;
            }

            return true;
        } catch (\UnexpectedValueException $e) {
            throw new \Exception('Invalid payload: ' . $e->getMessage());
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            throw new \Exception('Invalid signature: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Webhook processing error: ' . $e->getMessage());
        }
    }

    public function getPaymentStatus($stripePaymentId)
    {
        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($stripePaymentId);
            return $paymentIntent->status;
        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve payment status: ' . $e->getMessage());
        }
    }

    public function refundPayment($stripePaymentId, $amount = null)
    {
        try {
            $refundParams = ['payment_intent' => $stripePaymentId];
            if ($amount !== null) {
                $refundParams['amount'] = (int)($amount * 100); // Convert to cents
            }

            $refund = \Stripe\Refund::create($refundParams);
            
            // Update payment record
            $this->subscriptionHelper->handleRefund($stripePaymentId, $amount);

            return $refund;
        } catch (\Exception $e) {
            throw new \Exception('Failed to process refund: ' . $e->getMessage());
        }
    }

    public function getPublishableKey()
    {
        if (!$this->integration || !$this->integration->getIntegrationSettings()->getIsPublished()) {
            return null;
        }

        $keys = $this->integration->getDecryptedApiKeys();
        return $keys['stripe_publishable_key'] ?? null;
    }
}