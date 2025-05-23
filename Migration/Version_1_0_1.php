<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Mautic\IntegrationsBundle\Migration\AbstractMigration;

class Version_1_0_1 extends AbstractMigration
{
    private $subscriptionRatesTable = 'lodge_subscription_rates';
    private $paymentsTable = 'lodge_payments';

    protected function isApplicable(Schema $schema): bool
    {
        try {
            return !$schema->hasTable($this->concatPrefix($this->subscriptionRatesTable)) || 
                   !$schema->hasTable($this->concatPrefix($this->paymentsTable));
        } catch (SchemaException $e) {
            return false;
        }
    }

    protected function up(): void
    {
        // Create subscription rates table
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `{$this->concatPrefix($this->subscriptionRatesTable)}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `year` int(11) NOT NULL,
                `amount` decimal(10,2) NOT NULL,
                `description` varchar(255) DEFAULT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `year_unique` (`year`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create payments table
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `{$this->concatPrefix($this->paymentsTable)}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `contact_id` int(11) NOT NULL,
                `amount` decimal(10,2) NOT NULL,
                `year` int(11) NOT NULL,
                `payment_method` varchar(50) NOT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `applied_to_current` decimal(10,2) DEFAULT 0.00,
                `applied_to_arrears` decimal(10,2) DEFAULT 0.00,
                `notes` text,
                `stripe_payment_id` varchar(255) DEFAULT NULL,
                `received_by` varchar(255) DEFAULT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `contact_id` (`contact_id`),
                KEY `year` (`year`),
                KEY `status` (`status`),
                KEY `payment_method` (`payment_method`),
                KEY `stripe_payment_id` (`stripe_payment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
} 