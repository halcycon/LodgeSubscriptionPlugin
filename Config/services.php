<?php

declare(strict_types=1);

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use MauticPlugin\LodgeSubscriptionBundle\Controller\ReportController;
use MauticPlugin\LodgeSubscriptionBundle\Controller\WebhookController;
use MauticPlugin\LodgeSubscriptionBundle\Controller\RateController;
use MauticPlugin\LodgeSubscriptionBundle\Controller\SubscriptionController;
use MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel;
use MauticPlugin\LodgeSubscriptionBundle\Services\StripeService;
use MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Entity'
    ];

    $services->load('MauticPlugin\\LodgeSubscriptionBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MauticCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    
    // Explicitly register the integration
    $services->set('mautic.integration.lodgesubscription', \MauticPlugin\LodgeSubscriptionBundle\Integration\LodgeSubscriptionIntegration::class)
        ->public()
        ->tag('mautic.integration');
    
    // Register controllers explicitly to ensure proper autowiring
    $services->set(ReportController::class)
        ->public()
        ->tag('controller.service_arguments');
    
    $services->set(WebhookController::class)
        ->public()
        ->tag('controller.service_arguments');
    
    $services->set(RateController::class)
        ->public()
        ->tag('controller.service_arguments');
    
    $services->set(SubscriptionController::class)
        ->public()
        ->tag('controller.service_arguments');

    // Add service aliases for backward compatibility
    $services->alias('mautic.lodge.service.stripe', \MauticPlugin\LodgeSubscriptionBundle\Services\StripeService::class);
    $services->alias('mautic.lodge.helper.subscription', \MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper::class);
    $services->alias('mautic.lodge.model.subscription', \MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel::class);

    // Auto-register all classes in specific directories
    $services->load('MauticPlugin\\LodgeSubscriptionBundle\\Controller\\', '../Controller/')
        ->tag('controller.service_arguments')
        ->public();
        
    $services->load('MauticPlugin\\LodgeSubscriptionBundle\\Model\\', '../Model/');
    $services->load('MauticPlugin\\LodgeSubscriptionBundle\\Services\\', '../Services/');
    $services->load('MauticPlugin\\LodgeSubscriptionBundle\\Helper\\', '../Helper/');
}; 