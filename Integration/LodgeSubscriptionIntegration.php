<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

class LodgeSubscriptionIntegration extends AbstractIntegration
{
    const INTEGRATION_NAME = 'LodgeSubscription';

    public function getName(): string
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName(): string
    {
        return 'Lodge Subscription Manager';
    }

    public function getAuthenticationType(): string
    {
        return 'none';
    }

    public function getRequiredKeyFields(): array
    {
        return [
            'stripe_publishable_key' => 'mautic.lodge.stripe.publishable.key',
            'stripe_secret_key'      => 'mautic.lodge.stripe.secret.key',
            'stripe_webhook_secret'  => 'mautic.lodge.stripe.webhook.secret'
        ];
    }

    public function getFormSettings(): array
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false
        ];
    }
    
    /**
     * Initialize the integration
     */
    public function init(): void
    {
        // Do nothing
    }
    
    /**
     * Return plugin features
     */
    public function getSupportedFeatures(): array
    {
        return [];
    }
}