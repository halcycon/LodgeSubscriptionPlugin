<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CoreBundle\Entity\CommonEntity;

class SubscriptionRate extends CommonEntity
{
    private $id;
    private $year;
    private $amount;
    private $description;
    private $dateAdded;
    private $dateModified;

    public static function loadMetadata(ClassMetadata $metadata): void
    {
        $metadata->setTableName('lodge_subscription_rates');
        $metadata->setCustomRepositoryClass('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRateRepository');

        // ID field
        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'integer',
            'id' => true,
            'options' => [
                'autoincrement' => true,
            ],
        ]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);

        // Year
        $metadata->mapField([
            'fieldName' => 'year',
            'type' => 'integer',
        ]);

        // Amount
        $metadata->mapField([
            'fieldName' => 'amount',
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        // Description
        $metadata->mapField([
            'fieldName' => 'description',
            'type' => 'string',
            'length' => 255,
            'nullable' => true,
        ]);

        // Date Added
        $metadata->mapField([
            'fieldName' => 'dateAdded',
            'type' => 'datetime',
            'columnName' => 'date_added',
        ]);

        // Date Modified
        $metadata->mapField([
            'fieldName' => 'dateModified',
            'type' => 'datetime',
            'nullable' => true,
            'columnName' => 'date_modified',
        ]);
    }

    public function __construct()
    {
        $this->dateAdded = new \DateTime();
    }

    // Getters and setters
    public function getId()
    {
        return $this->id;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    public function getDateModified()
    {
        return $this->dateModified;
    }

    public function setDateModified(\DateTime $dateModified = null)
    {
        $this->dateModified = $dateModified;
        return $this;
    }
}