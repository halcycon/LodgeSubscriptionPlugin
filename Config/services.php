<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Enable autowiring for all services in this bundle
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    // Register all classes in the bundle for autowiring
    $services->load('MauticPlugin\\LodgeSubscriptionBundle\\', '../')
        ->exclude([
            '../{Config,DependencyInjection,Entity,Migration,Tests}',
            '../*Bundle.php',
        ]);

    // Explicit service definitions for better clarity and control
    $services->set('MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel')
        ->autowire()
        ->public();

    $services->set('MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper')
        ->autowire()
        ->public();

    $services->set('MauticPlugin\LodgeSubscriptionBundle\Services\StripeService')
        ->autowire()
        ->public();

    $services->set('MauticPlugin\LodgeSubscriptionBundle\Controller\ReportController')
        ->autowire()
        ->public()
        ->tag('controller.service_arguments');

    $services->set('MauticPlugin\LodgeSubscriptionBundle\Controller\WebhookController')
        ->autowire()
        ->public()
        ->tag('controller.service_arguments');

    $services->set('MauticPlugin\LodgeSubscriptionBundle\Controller\RateController')
        ->autowire()
        ->public()
        ->tag('controller.service_arguments');

    $services->set('MauticPlugin\LodgeSubscriptionBundle\Form\Type\SubscriptionRateType')
        ->autowire()
        ->public()
        ->tag('form.type');

    // Service aliases for backward compatibility
    $services->alias('lodge_subscription.model.subscription', 'MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel');
    $services->alias('lodge_subscription.helper.subscription', 'MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper');
    $services->alias('lodge_subscription.services.stripe', 'MauticPlugin\LodgeSubscriptionBundle\Services\StripeService');
}; 