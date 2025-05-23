<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class LodgeSubscriptionExtension extends Extension
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Config'));
        
        // Load services if the file exists
        if (file_exists(__DIR__.'/../Config/services.php')) {
            $loader->load('services.php');
        }
    }
} 