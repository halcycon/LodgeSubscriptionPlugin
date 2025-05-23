<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CoreBundle\Entity\CommonEntity;

class Payment extends CommonEntity
{
    private $id;
    private $contactId;
    private $amount;
    private $year;
    private $stripePaymentId;
    private $paymentMethod; // 'stripe', 'cash', 'cheque', etc.
    private $status; // 'completed', 'pending', 'failed'
    private $dateAdded;
    private $notes;
    private $appliedToCurrent;
    private $appliedToArrears;
    private $receivedBy;

    public static function loadMetadata(ClassMetadata $metadata): void
    {
        $metadata->setTableName('lodge_payments');
        $metadata->setCustomRepositoryClass('MauticPlugin\LodgeSubscriptionBundle\Entity\PaymentRepository');

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

        // Contact ID
        $metadata->mapField([
            'fieldName' => 'contactId',
            'type' => 'integer',
            'columnName' => 'contact_id',
        ]);

        // Amount
        $metadata->mapField([
            'fieldName' => 'amount',
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        // Year
        $metadata->mapField([
            'fieldName' => 'year',
            'type' => 'integer',
        ]);

        // Stripe Payment ID
        $metadata->mapField([
            'fieldName' => 'stripePaymentId',
            'type' => 'string',
            'length' => 255,
            'nullable' => true,
            'columnName' => 'stripe_payment_id',
        ]);

        // Payment Method
        $metadata->mapField([
            'fieldName' => 'paymentMethod',
            'type' => 'string',
            'length' => 50,
            'columnName' => 'payment_method',
        ]);

        // Status
        $metadata->mapField([
            'fieldName' => 'status',
            'type' => 'string',
            'length' => 50,
        ]);

        // Date Added
        $metadata->mapField([
            'fieldName' => 'dateAdded',
            'type' => 'datetime',
            'columnName' => 'date_added',
        ]);

        // Notes
        $metadata->mapField([
            'fieldName' => 'notes',
            'type' => 'text',
            'nullable' => true,
        ]);

        // Applied to Current
        $metadata->mapField([
            'fieldName' => 'appliedToCurrent',
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
            'columnName' => 'applied_to_current',
        ]);

        // Applied to Arrears
        $metadata->mapField([
            'fieldName' => 'appliedToArrears',
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
            'columnName' => 'applied_to_arrears',
        ]);

        // Received By
        $metadata->mapField([
            'fieldName' => 'receivedBy',
            'type' => 'string',
            'length' => 255,
            'nullable' => true,
            'columnName' => 'received_by',
        ]);
    }

    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->status = 'pending';
    }

    // Getters and Setters
    public function getId()
    {
        return $this->id;
    }

    public function getContactId()
    {
        return $this->contactId;
    }

    public function setContactId($contactId)
    {
        $this->contactId = $contactId;
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

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function getStripePaymentId()
    {
        return $this->stripePaymentId;
    }

    public function setStripePaymentId($stripePaymentId)
    {
        $this->stripePaymentId = $stripePaymentId;
        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    public function getAppliedToCurrent()
    {
        return $this->appliedToCurrent;
    }

    public function setAppliedToCurrent($amount)
    {
        $this->appliedToCurrent = $amount;
        return $this;
    }

    public function getAppliedToArrears()
    {
        return $this->appliedToArrears;
    }

    public function setAppliedToArrears($amount)
    {
        $this->appliedToArrears = $amount;
        return $this;
    }

    public function getReceivedBy()
    {
        return $this->receivedBy;
    }

    public function setReceivedBy($receivedBy)
    {
        $this->receivedBy = $receivedBy;
        return $this;
    }
}