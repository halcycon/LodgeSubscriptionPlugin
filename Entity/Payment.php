// Entity/Payment.php
<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Entity\CommonEntity;

/**
 * @ORM\Table(name="lodge_payments")
 * @ORM\Entity(repositoryClass="MauticPlugin\LodgeSubscriptionPlugin\Entity\PaymentRepository")
 */
class Payment extends CommonEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $contactId;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stripePaymentId;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $paymentMethod; // 'stripe', 'cash', 'cheque', etc.

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status; // 'completed', 'pending', 'failed'

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAdded;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $appliedToCurrent;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $appliedToArrears;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $receivedBy;

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