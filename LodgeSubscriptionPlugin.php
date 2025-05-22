<?php
namespace MauticPlugin\LodgeSubscriptionPlugin;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;

class LodgeSubscriptionPlugin extends PluginBundleBase
{
    public static function onPluginInstall(Plugin $plugin, $em, $schema)
    {
        $tool = new SchemaTool($em);
        $entities = [
            $em->getClassMetadata('MauticPlugin\LodgeSubscriptionPlugin\Entity\SubscriptionRate'),
            $em->getClassMetadata('MauticPlugin\LodgeSubscriptionPlugin\Entity\Payment')
        ];

        $tool->createSchema($entities);
    }

    public static function onPluginUpdate(Plugin $plugin, $em, $schema, $fromVersion)
    {
        // Handle plugin updates here
    }
}