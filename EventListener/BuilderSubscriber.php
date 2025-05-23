<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\EventListener;

use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BuilderSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_BUILD => ['onBuilderBuild', 0],
        ];
    }

    /**
     * Add tokens to the email builder
     */
    public function onBuilderBuild(EmailBuilderEvent $event)
    {
        $tokens = [
            '{lodge.subscription_amount}' => 'Current Subscription Amount',
            '{lodge.subscription_arrears}' => 'Arrears Amount',
            '{lodge.subscription_total}' => 'Total Amount Due',
            '{lodge.payment_link}' => 'Payment Link'
        ];

        $event->addTokens(
            $event->tokensFromArray('Lodge Subscription', $tokens)
        );
    }
} 