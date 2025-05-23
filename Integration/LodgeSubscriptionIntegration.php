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
        return 'keys';
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

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        // Return true if keys are set
        $keys = $this->getKeys();
        return !empty($keys['stripe_publishable_key']) && !empty($keys['stripe_secret_key']);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigFormSettings(): array
    {
        return [
            'keys' => [
                'label' => 'mautic.integration.form.keys',
                'data'  => $this->getFormSettings(),
                'notes' => [
                    'mautic.lodge.form.stripe.api.keys',
                ],
            ],
            'settings' => [
                'label'       => 'mautic.integration.form.settings',
                'form_type'   => 'lodgesubscriptionconfig',
                'data'        => $this->getFormLeadFields(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormLeadFields(): array
    {
        // Return configured fields
        return [
            'lodge_subscription_currency' => $this->getIntegrationSettings()->getFeatureSettings()['lodge_subscription_currency'] ?? 'GBP',
            'lodge_subscription_reminder_template' => $this->getIntegrationSettings()->getFeatureSettings()['lodge_subscription_reminder_template'] ?? null,
        ];
    }
}