<?php

declare(strict_types=1);

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

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
    
    // Add service aliases for backward compatibility
    $services->alias('mautic.lodge.service.stripe', \MauticPlugin\LodgeSubscriptionBundle\Services\StripeService::class);
    $services->alias('mautic.lodge.helper.subscription', \MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper::class);
    $services->alias('mautic.lodge.model.subscription', \MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel::class);
}; 