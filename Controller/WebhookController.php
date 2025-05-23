<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use MauticPlugin\LodgeSubscriptionBundle\Services\StripeService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CommonController
{
    protected StripeService $stripeService;
    protected LoggerInterface $logger;
    
    public function __construct(
        StripeService $stripeService,
        LoggerInterface $logger,
        RequestStack $requestStack
    ) {
        $this->stripeService = $stripeService;
        $this->logger = $logger;
        
        // Initialize the parent CommonController with the required RequestStack
        $this->requestStack = $requestStack;
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
}