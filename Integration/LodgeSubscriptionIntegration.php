<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

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
        return 'none';
    }

    public function getRequiredKeyFields(): array
    {
        return [
            'stripe_publishable_key' => 'mautic.lodge.stripe.publishable.key',
            'stripe_secret_key'      => 'mautic.lodge.stripe.secret.key',
            'stripe_webhook_secret'  => 'mautic.lodge.stripe.webhook.secret',
        ];
    }

    /**
     * Override the form field definitions
     */
    public function modifyForm($builder, $options): void
    {
        if (!empty($options['form_area']) && $options['form_area'] === 'keys') {
            // Define stripe_publishable_key as TextType
            $builder->add('stripe_publishable_key', TextType::class, [
                'label'      => 'mautic.lodge.stripe.publishable.key',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control no-datepicker',
                    'placeholder' => 'pk_test_...',
                    'data-toggle' => 'NO',
                    'data-no-calendar' => 'true'
                ],
                'required'    => true,
            ]);
            
            // Define stripe_secret_key as PasswordType
            $builder->add('stripe_secret_key', PasswordType::class, [
                'label'      => 'mautic.lodge.stripe.secret.key',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'sk_test_...',
                    'autocomplete' => 'off',
                ],
                'required'    => true,
            ]);
            
            // Define stripe_webhook_secret as PasswordType
            $builder->add('stripe_webhook_secret', PasswordType::class, [
                'label'      => 'mautic.lodge.stripe.webhook.secret',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'whsec_...',
                    'autocomplete' => 'off',
                ],
                'required'    => true,
            ]);
        }
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
        
        // Add custom settings tab
        $settings['featureSettings'] = [
            'label' => 'mautic.integration.form.feature.settings',
            'data'  => [
                'lodge_subscription_currency' => 'GBP',
                'lodge_subscription_reminder_template' => null,
            ],
            'formType' => 'lodgesubscriptionconfig',
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