<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

class LodgeSubscriptionIntegration extends AbstractIntegration
{
    public const INTEGRATION_NAME = 'LodgeSubscription';

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
            'stripe_publishable_key' => [
                'label' => 'mautic.lodge.stripe.publishable.key',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'pk_test_...',
                'tooltip' => 'mautic.lodge.stripe.publishable.key.tooltip',
            ],
            'stripe_secret_key' => [
                'label' => 'mautic.lodge.stripe.secret.key',
                'type' => 'password',
                'required' => true,
                'placeholder' => 'sk_test_...',
                'tooltip' => 'mautic.lodge.stripe.secret.key.tooltip',
            ],
            'stripe_webhook_secret' => [
                'label' => 'mautic.lodge.stripe.webhook.secret',
                'type' => 'password',
                'required' => true,
                'placeholder' => 'whsec_...',
                'tooltip' => 'mautic.lodge.stripe.webhook.secret.tooltip',
            ],
        ];
    }

    public function getFormSettings(): array
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
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
        $settings = parent::getConfigFormSettings();
        
        $settings['features'] = [
            'label' => 'mautic.integration.form.feature.settings',
            'data'  => [
                'lodge_subscription_currency' => 'GBP',
                'lodge_subscription_reminder_template' => null,
            ],
            'form_type' => 'lodgesubscriptionconfig',
        ];
        
        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormLeadFields(array $settings = []): array
    {
        // Return configured fields
        $featureSettings = $this->getIntegrationSettings()->getFeatureSettings();
        
        return [
            'lodge_subscription_currency' => $featureSettings['lodge_subscription_currency'] ?? 'GBP',
            'lodge_subscription_reminder_template' => $featureSettings['lodge_subscription_reminder_template'] ?? null,
        ];
    }
}