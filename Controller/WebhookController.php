<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use MauticPlugin\LodgeSubscriptionBundle\Services\StripeService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    protected StripeService $stripeService;
    protected LoggerInterface $logger;
    
    public function __construct(
        StripeService $stripeService,
        LoggerInterface $logger
    ) {
        $this->stripeService = $stripeService;
        $this->logger = $logger;
    }

    /**
     * Handle Stripe webhook
     */
    public function handleAction(Request $request): Response
    {
        // Retrieve the request's body and parse it as JSON
        $payload = $request->getContent();

        // Get the Stripe-Signature header
        $sigHeader = $request->headers->get('Stripe-Signature');

        if (!$sigHeader || !$payload) {
            return new Response('Invalid webhook request', 400);
        }

        try {
            $result = $this->stripeService->handleWebhook($payload, $sigHeader);

            if ($result) {
                $this->logger->info('Stripe webhook processed successfully');
                return new Response('Webhook handled successfully', 200);
            } else {
                return new Response('Webhook processing failed', 500);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error processing Stripe webhook: ' . $e->getMessage());
            return new Response($e->getMessage(), 400);
        }
    }

    /**
     * Test endpoint to verify webhook is accessible (GET request)
     */
    public function testAction(): Response
    {
        return new Response(json_encode([
            'status' => 'Webhook endpoint is accessible',
            'note' => 'This is a test endpoint. The actual webhook only accepts POST requests with valid Stripe signatures.',
            'webhook_url' => '/lodge/webhook/stripe',
            'method' => 'POST only'
        ]), 200, ['Content-Type' => 'application/json']);
    }
}