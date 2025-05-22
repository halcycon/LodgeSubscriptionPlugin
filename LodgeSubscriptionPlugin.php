<?php
namespace MauticPlugin\LodgeSubscriptionPlugin;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LodgeSubscriptionPlugin extends PluginBundleBase
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    /**
     * Called when plugin is installed
     */
    public static function onPluginInstall(Plugin $plugin, $em, $schema)
    {
        $tool = new SchemaTool($em);
        $entities = [
            $em->getClassMetadata('MauticPlugin\LodgeSubscriptionPlugin\Entity\SubscriptionRate'),
            $em->getClassMetadata('MauticPlugin\LodgeSubscriptionPlugin\Entity\Payment')
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
}