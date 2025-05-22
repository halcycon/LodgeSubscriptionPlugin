<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Model;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Model\AbstractCommonModel;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\LodgeSubscriptionPlugin\Entity\Payment;
use MauticPlugin\LodgeSubscriptionPlugin\Entity\SubscriptionRate;

class SubscriptionModel extends AbstractCommonModel
{
    /**
     * Get repository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticPlugin\LodgeSubscriptionPlugin\Entity\SubscriptionRate');
    }

    /**
     * Get payment repository
     */
    public function getPaymentRepository()
    {
        return $this->em->getRepository('MauticPlugin\LodgeSubscriptionPlugin\Entity\Payment');
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
        if ($year === null) {
            $year = date('Y');
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(l) as totalCount, SUM(l.craft_owed_current) as currentTotal, SUM(l.craft_owed_arrears) as arrearsTotal')
           ->from('MauticLeadBundle:Lead', 'l')
           ->where("l.craft_{$year}_due = :due")
           ->setParameter('due', 1);

        $result = $qb->getQuery()->getSingleResult();

        $paidQb = $this->em->createQueryBuilder();
        $paidQb->select('COUNT(l) as paidCount')
               ->from('MauticLeadBundle:Lead', 'l')
               ->where("l.craft_{$year}_due = :due")
               ->andWhere("l.craft_{$year}_paid = :paid")
               ->setParameter('due', 1)
               ->setParameter('paid', 1);

        $paidResult = $paidQb->getQuery()->getSingleResult();

        return [
            'totalMembers' => $result['totalCount'] ?? 0,
            'paidMembers' => $paidResult['paidCount'] ?? 0,
            'unpaidMembers' => ($result['totalCount'] ?? 0) - ($paidResult['paidCount'] ?? 0),
            'currentTotal' => $result['currentTotal'] ?? 0,
            'arrearsTotal' => $result['arrearsTotal'] ?? 0,
            'grandTotal' => ($result['currentTotal'] ?? 0) + ($result['arrearsTotal'] ?? 0)
        ];
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