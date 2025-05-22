<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

class LodgeSubscriptionIntegration extends AbstractIntegration
{
    const INTEGRATION_NAME = 'LodgeSubscription';

    public function getName()
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName()
    {
        return 'Lodge Subscription Manager';
    }

    public function getAuthenticationType()
    {
        return 'none';
    }

    public function getRequiredKeyFields()
    {
        return [
            'stripe_publishable_key' => 'mautic.lodge.stripe.publishable.key',
            'stripe_secret_key'      => 'mautic.lodge.stripe.secret.key',
            'stripe_webhook_secret'  => 'mautic.lodge.stripe.webhook.secret'
        ];
    }

    public function getFormSettings()
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false
        ];
    }
}