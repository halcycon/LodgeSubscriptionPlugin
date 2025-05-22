<?php
namespace MauticPlugin\LodgeSubscriptionPlugin;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LodgeSubscriptionBundle extends PluginBundleBase
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'LodgeSubscriptionBundle';
    }
} 