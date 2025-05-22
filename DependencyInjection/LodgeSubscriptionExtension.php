<?php

namespace MauticPlugin\LodgeSubscriptionPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LodgeSubscriptionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Load YAML config if available
        $yamlLoader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Config'));
        if (file_exists(__DIR__.'/../Config/services.yaml')) {
            $yamlLoader->load('services.yaml');
        }

        // Load PHP config 
        $phpLoader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Config'));
        if (file_exists(__DIR__.'/../Config/config.php')) {
            $phpLoader->load('config.php');
        }
    }
} 