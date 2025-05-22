<?php
namespace MauticPlugin\LodgeSubscriptionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Entity\CommonEntity;

/**
 * @ORM\Table(name="lodge_subscription_rates")
 * @ORM\Entity(repositoryClass="MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRateRepository")
 */
class SubscriptionRate extends CommonEntity
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
    private $year;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAdded;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateModified;

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