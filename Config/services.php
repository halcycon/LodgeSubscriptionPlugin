<?php

declare(strict_types=1);

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use MauticPlugin\LodgeSubscriptionBundle\Form\Type\SubscriptionRateType;
use MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper;
use MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel;
use MauticPlugin\LodgeSubscriptionBundle\Services\StripeService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    // Exclude directories that shouldn't be autowired as services
    $excludes = [
        'Entity',           // Doctrine entities and repositories
        'Migration',        // Database migrations
        'Tests',           // Test classes
        'Config',          // Configuration files
    ];

    $services->load('MauticPlugin\\LodgeSubscriptionBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MauticCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    // Specific service definitions with custom configuration
    $services->set(SubscriptionModel::class)
        ->args([
            '$entityManager' => '@doctrine.orm.entity_manager',
            '$leadModel' => '@mautic.lead.model.lead',
            '$userModel' => '@mautic.user.model.user',
            '$logger' => '@monolog.logger.mautic'
        ]);

    $services->set(SubscriptionHelper::class)
        ->args([
            '$leadModel' => '@mautic.lead.model.lead',
            '$entityManager' => '@doctrine.orm.entity_manager',
            '$userModel' => '@mautic.user.model.user'
        ]);

    $services->set(StripeService::class)
        ->args([
            '$integrationHelper' => '@mautic.helper.integration',
            '$router' => '@router',
            '$subscriptionHelper' => '@'.SubscriptionHelper::class
        ]);

    $services->set(SubscriptionRateType::class)
        ->tag('form.type');

    // Backward compatibility aliases
    $services->alias('lodge_subscription.model.subscription', SubscriptionModel::class);
    $services->alias('lodge_subscription.helper.subscription', SubscriptionHelper::class);
    $services->alias('lodge_subscription.services.stripe', StripeService::class);
    $services->alias('lodge_subscription.form.type.subscription_rate', SubscriptionRateType::class);
}; 