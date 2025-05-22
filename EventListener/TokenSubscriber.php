<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\EventListener;

use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailSendEvent;
use MauticPlugin\LodgeSubscriptionPlugin\Services\StripeService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TokenSubscriber implements EventSubscriberInterface
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_SEND => ['onEmailSend', 0],
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailDisplay', 0],
        ];
    }

    /**
     * Add tokens when email is sent
     */
    public function onEmailSend(EmailSendEvent $event)
    {
        $this->injectTokens($event);
    }

    /**
     * Add tokens when email is displayed
     */
    public function onEmailDisplay(EmailSendEvent $event)
    {
        $this->injectTokens($event);
    }

    /**
     * Inject payment tokens
     */
    private function injectTokens(EmailSendEvent $event)
    {
        // Get the lead/contact
        $lead = $event->getLead();
        if (empty($lead)) {
            return;
        }

        $contactId = $lead['id'];
        $email = $lead['email'];

        if (empty($email)) {
            return;
        }

        $tokens = [];

        // Process {lodge.subscription_amount} token
        if ($event->getTokens()->hasTokens('{lodge.subscription_amount}')) {
            $currentOwed = isset($lead['craft_owed_current']) ? (float)$lead['craft_owed_current'] : 0;
            $tokens['{lodge.subscription_amount}'] = number_format($currentOwed, 2);
        }

        // Process {lodge.subscription_arrears} token
        if ($event->getTokens()->hasTokens('{lodge.subscription_arrears}')) {
            $arrearsOwed = isset($lead['craft_owed_arrears']) ? (float)$lead['craft_owed_arrears'] : 0;
            $tokens['{lodge.subscription_arrears}'] = number_format($arrearsOwed, 2);
        }

        // Process {lodge.subscription_total} token
        if ($event->getTokens()->hasTokens('{lodge.subscription_total}')) {
            $currentOwed = isset($lead['craft_owed_current']) ? (float)$lead['craft_owed_current'] : 0;
            $arrearsOwed = isset($lead['craft_owed_arrears']) ? (float)$lead['craft_owed_arrears'] : 0;
            $total = $currentOwed + $arrearsOwed;
            $tokens['{lodge.subscription_total}'] = number_format($total, 2);
        }

        // Process {lodge.payment_link} token - this generates a Stripe payment link
        if ($event->getTokens()->hasTokens('{lodge.payment_link}')) {
            $currentOwed = isset($lead['craft_owed_current']) ? (float)$lead['craft_owed_current'] : 0;
            $arrearsOwed = isset($lead['craft_owed_arrears']) ? (float)$lead['craft_owed_arrears'] : 0;
            $total = $currentOwed + $arrearsOwed;

            if ($total > 0) {
                try {
                    $session = $this->stripeService->createCheckoutSession($contactId, $total, $email);
                    $tokens['{lodge.payment_link}'] = $session->url;
                } catch (\Exception $e) {
                    // Default fallback if Stripe is not configured
                    $tokens['{lodge.payment_link}'] = '#';
                }
            } else {
                $tokens['{lodge.payment_link}'] = '#';
            }
        }

        // Add tokens to the event
        if (!empty($tokens)) {
            $event->addTokens($tokens);
        }
    }
} 