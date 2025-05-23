<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle;

use Mautic\IntegrationsBundle\Bundle\AbstractPluginBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LodgeSubscriptionBundle extends AbstractPluginBundle
{
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