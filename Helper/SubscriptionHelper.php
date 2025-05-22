<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Helper;

use Mautic\LeadBundle\Model\LeadModel;
use Doctrine\ORM\EntityManager;
use MauticPlugin\LodgeSubscriptionPlugin\Entity\Payment;
use Mautic\UserBundle\Model\UserModel;

class SubscriptionHelper
{
    private $leadModel;
    private $entityManager;
    private $userModel;

    public function __construct(
        LeadModel $leadModel, 
        EntityManager $entityManager,
        UserModel $userModel
    ) {
        $this->leadModel = $leadModel;
        $this->entityManager = $entityManager;
        $this->userModel = $userModel;
    }

    /**
     * Record a new payment or update existing payment
     */
    public function recordPayment(
        $contactId, 
        $amount, 
        $year, 
        $paymentMethod, 
        $stripePaymentId = null, 
        $status = 'pending'
    ) {
        // If Stripe payment ID exists, try to find existing payment
        $payment = null;
        if ($stripePaymentId) {
            $payment = $this->entityManager->getRepository(Payment::class)
                ->findOneBy(['stripePaymentId' => $stripePaymentId]);
        }

        // If no existing payment, create new one
        if (!$payment) {
            $payment = new Payment();
            $payment->setContactId($contactId)
                   ->setAmount($amount)
                   ->setYear($year)
                   ->setPaymentMethod($paymentMethod)
                   ->setStripePaymentId($stripePaymentId);
        }

        $contact = $this->leadModel->getEntity($contactId);
        if (!$contact) {
            throw new \Exception('Contact not found');
        }

        // Calculate payment distribution
        $currentOwed = (float)$contact->getFieldValue('craft_owed_current');
        $arrearsOwed = (float)$contact->getFieldValue('craft_owed_arrears');

        $appliedToCurrent = min($currentOwed, $amount);
        $remainingAmount = $amount - $appliedToCurrent;
        $appliedToArrears = min($arrearsOwed, $remainingAmount);

        $payment->setAppliedToCurrent($appliedToCurrent)
               ->setAppliedToArrears($appliedToArrears)
               ->setStatus($status)
               ->setReceivedBy($this->userModel->getCurrentUser()->getName());

        // If payment is completed, update contact fields
        if ($status === 'completed') {
            $this->updateContactPaymentStatus($contactId, $amount, $year);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $payment;
    }

    /**
     * Update contact fields after successful payment
     */
    public function updateContactPaymentStatus($contactId, $amount, $year)
    {
        $contact = $this->leadModel->getEntity($contactId);
        if (!$contact) {
            throw new \Exception('Contact not found');
        }

        $currentOwed = (float)$contact->getFieldValue('craft_owed_current');
        $arrearsOwed = (float)$contact->getFieldValue('craft_owed_arrears');

        // Apply payment to current year first
        $appliedToCurrent = min($currentOwed, $amount);
        $remainingAmount = $amount - $appliedToCurrent;
        
        // Apply remaining to arrears if any
        $appliedToArrears = min($arrearsOwed, $remainingAmount);

        // Update amounts owed
        $newCurrentOwed = max(0, $currentOwed - $appliedToCurrent);
        $newArrearsOwed = max(0, $arrearsOwed - $appliedToArrears);

        // Update contact fields
        $contact->addUpdatedField('craft_owed_current', $newCurrentOwed);
        $contact->addUpdatedField('craft_owed_arrears', $newArrearsOwed);

        // If current year is fully paid
        if ($newCurrentOwed == 0) {
            $contact->addUpdatedField("craft_{$year}_paid", true);
            $contact->addUpdatedField('craft_paid_current', true);
        }

        $this->leadModel->saveEntity($contact);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($stripePaymentId, $status, $notes = null)
    {
        $payment = $this->entityManager->getRepository(Payment::class)
            ->findOneBy(['stripePaymentId' => $stripePaymentId]);

        if (!$payment) {
            throw new \Exception('Payment not found');
        }

        $payment->setStatus($status);
        if ($notes) {
            $payment->setNotes($notes);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        // If payment failed, we might need to reverse any provisional credits
        if ($status === 'failed') {
            $this->reversePaymentCredits($payment);
        }

        return $payment;
    }

    /**
     * Handle refund processing
     */
    public function handleRefund($stripePaymentId, $refundAmount)
    {
        $payment = $this->entityManager->getRepository(Payment::class)
            ->findOneBy(['stripePaymentId' => $stripePaymentId]);

        if (!$payment) {
            throw new \Exception('Payment not found');
        }

        // Create refund record
        $refund = new Payment();
        $refund->setContactId($payment->getContactId())
               ->setAmount(-$refundAmount) // Negative amount for refund
               ->setYear($payment->getYear())
               ->setPaymentMethod('stripe_refund')
               ->setStripePaymentId($stripePaymentId . '_refund')
               ->setStatus('completed')
               ->setNotes('Refund for payment ' . $payment->getId())
               ->setReceivedBy($this->userModel->getCurrentUser()->getName());

        // Reverse the payment applications proportionally
        $totalOriginal = $payment->getAppliedToCurrent() + $payment->getAppliedToArrears();
        if ($totalOriginal > 0) {
            $refundToCurrent = ($payment->getAppliedToCurrent() / $totalOriginal) * $refundAmount;
            $refundToArrears = ($payment->getAppliedToArrears() / $totalOriginal) * $refundAmount;

            $refund->setAppliedToCurrent(-$refundToCurrent)
                   ->setAppliedToArrears(-$refundToArrears);

            // Update contact balances
            $contact = $this->leadModel->getEntity($payment->getContactId());
            if ($contact) {
                $currentOwed = (float)$contact->getFieldValue('craft_owed_current');
                $arrearsOwed = (float)$contact->getFieldValue('craft_owed_arrears');

                $contact->addUpdatedField('craft_owed_current', $currentOwed + $refundToCurrent);
                $contact->addUpdatedField('craft_owed_arrears', $arrearsOwed + $refundToArrears);

                // If current year is no longer fully paid
                if ($currentOwed + $refundToCurrent > 0) {
                    $contact->addUpdatedField("craft_{$payment->getYear()}_paid", false);
                    $contact->addUpdatedField('craft_paid_current', false);
                }

                $this->leadModel->saveEntity($contact);
            }
        }

        $this->entityManager->persist($refund);
        $this->entityManager->flush();

        return $refund;
    }

    /**
     * Reverse provisional credits for failed payments
     */
    private function reversePaymentCredits(Payment $payment)
    {
        $contact = $this->leadModel->getEntity($payment->getContactId());
        if (!$contact) {
            return;
        }

        $currentOwed = (float)$contact->getFieldValue('craft_owed_current');
        $arrearsOwed = (float)$contact->getFieldValue('craft_owed_arrears');

        // Reverse the credits
        $contact->addUpdatedField('craft_owed_current', 
            $currentOwed + $payment->getAppliedToCurrent());
        $contact->addUpdatedField('craft_owed_arrears', 
            $arrearsOwed + $payment->getAppliedToArrears());

        // Reset payment status flags if necessary
        if ($payment->getAppliedToCurrent() > 0) {
            $contact->addUpdatedField("craft_{$payment->getYear()}_paid", false);
            $contact->addUpdatedField('craft_paid_current', false);
        }

        $this->leadModel->saveEntity($contact);
    }

    /**
     * Get payment history for a contact
     */
    public function getContactPaymentHistory($contactId, $limit = 10)
    {
        return $this->entityManager->getRepository(Payment::class)
            ->getContactPayments($contactId, $limit);
    }

    /**
     * Get total payments for a year
     */
    public function getYearlyPaymentTotal($year)
    {
        return $this->entityManager->getRepository(Payment::class)
            ->getTotalPaymentsForYear($year);
    }
}