<?php
namespace MauticPlugin\LodgeSubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CommonController
{
    /**
     * Handle Stripe webhook
     */
    public function handleAction(Request $request)
    {
        // Retrieve the request's body and parse it as JSON
        $payload = $request->getContent();

        // Get the Stripe-Signature header
        $sigHeader = $request->headers->get('Stripe-Signature');

        if (!$sigHeader || !$payload) {
            return new Response('Invalid webhook request', 400);
        }

        try {
            $stripeService = $this->get('mautic.lodge.service.stripe');
            $result = $stripeService->handleWebhook($payload, $sigHeader);

            if ($result) {
                $this->get('monolog.logger.mautic')->info('Stripe webhook processed successfully');
                return new Response('Webhook handled successfully', 200);
            } else {
                return new Response('Webhook processing failed', 500);
            }
        } catch (\Exception $e) {
            $this->get('monolog.logger.mautic')->error('Error processing Stripe webhook: ' . $e->getMessage());
            return new Response($e->getMessage(), 400);
        }
    }
}