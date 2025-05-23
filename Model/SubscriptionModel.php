<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Model;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Model\AbstractCommonModel;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\LodgeSubscriptionBundle\Entity\Payment;
use MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate;

class SubscriptionModel extends AbstractCommonModel
{
    /**
     * Get repository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\SubscriptionRate');
    }

    /**
     * Get payment repository
     */
    public function getPaymentRepository()
    {
        return $this->em->getRepository('MauticPlugin\LodgeSubscriptionBundle\Entity\Payment');
    }

    /**
     * Get subscription rate for a year
     */
    public function getRateForYear($year)
    {
        return $this->getRepository()->findOneBy(['year' => $year]);
    }

    /**
     * Create new rate
     */
    public function saveRate(SubscriptionRate $rate)
    {
        $this->saveEntity($rate);
        return $rate;
    }

    /**
     * Get payments for a contact
     */
    public function getContactPayments($contactId, $limit = 10)
    {
        return $this->getPaymentRepository()->getContactPayments($contactId, $limit);
    }

    /**
     * Get contacts with unpaid subscriptions
     */
    public function getContactsWithUnpaidSubscriptions()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('l')
           ->from('MauticLeadBundle:Lead', 'l')
           ->where('l.craft_paid_current = :paid')
           ->andWhere('l.craft_owed_current > :zero')
           ->setParameter('paid', 0)
           ->setParameter('zero', 0);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get subscription status summary
     */
    public function getSubscriptionStatusSummary($year = null)
    {
        // Default values
        $defaultResult = [
            'totalMembers' => 0,
            'paidMembers' => 0,
            'unpaidMembers' => 0,
            'currentTotal' => 0,
            'arrearsTotal' => 0,
            'grandTotal' => 0
        ];

        if ($year === null) {
            $year = date('Y');
        }

        try {
            // Validate that year is a simple numeric value to prevent SQL injection
            if (!is_numeric($year)) {
                return $defaultResult;
            }
            
            $yearField = "craft_{$year}_due";
            $paidField = "craft_{$year}_paid";
            
            // Check if the field exists
            $metadata = $this->em->getClassMetadata('MauticLeadBundle:Lead');
            if (!$metadata->hasField($yearField) || !$metadata->hasField($paidField)) {
                return $defaultResult;
            }
            
            $qb = $this->em->createQueryBuilder();
            $qb->select('COUNT(l) as totalCount, SUM(l.craft_owed_current) as currentTotal, SUM(l.craft_owed_arrears) as arrearsTotal')
               ->from('MauticLeadBundle:Lead', 'l')
               ->where("l.{$yearField} = :due")
               ->setParameter('due', 1);

            $result = $qb->getQuery()->getOneOrNullResult();

            $paidQb = $this->em->createQueryBuilder();
            $paidQb->select('COUNT(l) as paidCount')
                   ->from('MauticLeadBundle:Lead', 'l')
                   ->where("l.{$yearField} = :due")
                   ->andWhere("l.{$paidField} = :paid")
                   ->setParameter('due', 1)
                   ->setParameter('paid', 1);

            $paidResult = $paidQb->getQuery()->getOneOrNullResult();

            return [
                'totalMembers' => (int)($result['totalCount'] ?? 0),
                'paidMembers' => (int)($paidResult['paidCount'] ?? 0),
                'unpaidMembers' => (int)(($result['totalCount'] ?? 0) - ($paidResult['paidCount'] ?? 0)),
                'currentTotal' => (float)($result['currentTotal'] ?? 0),
                'arrearsTotal' => (float)($result['arrearsTotal'] ?? 0),
                'grandTotal' => (float)(($result['currentTotal'] ?? 0) + ($result['arrearsTotal'] ?? 0))
            ];
        } catch (\Exception $e) {
            // Log error if logger is available
            if ($this->logger) {
                $this->logger->error(
                    'Error getting subscription status: ' . $e->getMessage(),
                    ['exception' => $e]
                );
            }
            return $defaultResult;
        }
    }

    /**
     * Create custom fields for a year
     */
    public function createCustomFieldsForYear($year, $fieldModel)
    {
        $fields = [
            [
                'label' => "Craft {$year} Due",
                'alias' => "craft_{$year}_due",
                'type' => 'boolean',
                'isPublished' => true,
                'group' => 'lodge_subscriptions'
            ],
            [
                'label' => "Craft {$year} Paid",
                'alias' => "craft_{$year}_paid",
                'type' => 'boolean',
                'isPublished' => true,
                'group' => 'lodge_subscriptions'
            ]
        ];

        $created = [];
        foreach ($fields as $field) {
            if (!$fieldModel->getEntityByAlias($field['alias'])) {
                $fieldEntity = $fieldModel->createCustomField($field);
                $created[] = $fieldEntity->getAlias();
            }
        }

        return $created;
    }
}