<?php
// This is a test file to check if the plugin classes are being loaded correctly

require_once dirname(__DIR__) . '/app/autoload.php';

echo "Testing LodgeSubscriptionBundle class loading...\n\n";

// Check if the bundle class exists
echo "LodgeSubscriptionBundle class: ";
var_dump(class_exists('MauticPlugin\LodgeSubscriptionBundle\LodgeSubscriptionBundle'));

// Check if the integration class exists
echo "Integration class: ";
var_dump(class_exists('MauticPlugin\LodgeSubscriptionBundle\Integration\LodgeSubscriptionIntegration'));

// Check bundle path
echo "\nBundle path: " . dirname(__FILE__) . "\n";

// Check if the entity classes exist
echo "Entity classes:\n";
echo "- SubscriptionRate: ";
var_dump(class_exists('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate'));
echo "- Payment: ";
var_dump(class_exists('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment'));

// Check if models exist
echo "\nModel classes:\n";
echo "- SubscriptionModel: ";
var_dump(class_exists('MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel'));

// Check controllers exist
echo "\nController classes:\n";
echo "- RateController: ";
var_dump(class_exists('MauticPlugin\LodgeSubscriptionBundle\Controller\RateController'));
echo "- SubscriptionController: ";
var_dump(class_exists('MauticPlugin\LodgeSubscriptionBundle\Controller\SubscriptionController'));

// Try to get the container
echo "\nAttempting to access Mautic kernel...\n";
try {
    $kernel = new \Mautic\CoreBundle\Console\Kernel(getcwd());
    $kernel->boot();
    $container = $kernel->getContainer();
    echo "Kernel booted successfully.\n";
    
    // Check if services are registered
    echo "\nChecking if services are registered:\n";
    echo "- mautic.lodge.model.subscription: ";
    var_dump($container->has('mautic.lodge.model.subscription'));
    echo "- mautic.lodge.service.stripe: ";
    var_dump($container->has('mautic.lodge.service.stripe'));
    echo "- mautic.lodge.helper.subscription: ";
    var_dump($container->has('mautic.lodge.helper.subscription'));
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 