<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Mautic\IntegrationsBundle\Bundle\AbstractPluginBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LodgeSubscriptionBundle extends AbstractPluginBundle
{
    /**
     * Called when plugin is installed
     */
    public static function onPluginInstall(Plugin $plugin, $em, $schema)
    {
        $tool = new SchemaTool($em);
        $entities = [
            $em->getClassMetadata('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate'),
            $em->getClassMetadata('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment')
        ];

        $tool->createSchema($entities);
    }

    /**
     * Called when plugin is updated
     */
    public static function onPluginUpdate(Plugin $plugin, $em, $schema, $fromVersion)
    {
        // Handle plugin updates here
    }
    
    /**
     * Load plugin assets
     */
    public static function loadAssets(MauticFactory $factory)
    {
        $factory->getAssetsHelper()->addStylesheet('plugins/LodgeSubscriptionBundle/Assets/css/lodge-subscription.css');
        $factory->getAssetsHelper()->addScript('plugins/LodgeSubscriptionBundle/Assets/js/lodge-subscription.js');
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
    
    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        parent::boot();
    }
} 