<?php
namespace MauticPlugin\LodgeSubscriptionBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;

class LodgeSubscriptionBundle extends PluginBundleBase
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
} 