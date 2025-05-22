<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Entity;

use Doctrine\ORM\EntityRepository;

class PaymentRepository extends EntityRepository
{
    /**
     * Get payments for a specific contact
     */
    public function getContactPayments($contactId, $limit = 10)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.contactId = :contactId')
           ->setParameter('contactId', $contactId)
           ->orderBy('p.dateAdded', 'DESC');
        
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Get all payments in a date range
     */
    public function getPaymentsInDateRange(\DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.dateAdded >= :startDate')
           ->andWhere('p.dateAdded <= :endDate')
           ->setParameter('startDate', $startDate)
           ->setParameter('endDate', $endDate)
           ->orderBy('p.dateAdded', 'DESC');
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Get total payments for a year
     */
    public function getTotalPaymentsForYear($year)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('SUM(p.amount) as total')
           ->where('p.year = :year')
           ->andWhere('p.status = :status')
           ->setParameter('year', $year)
           ->setParameter('status', 'completed');
        
        $result = $qb->getQuery()->getSingleScalarResult();
        
        return $result ? $result : 0;
    }
    
    /**
     * Get payments by status
     */
    public function getPaymentsByStatus($status, $limit = 10)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.status = :status')
           ->setParameter('status', $status)
           ->orderBy('p.dateAdded', 'DESC');
        
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Get payments by method
     */
    public function getPaymentsByMethod($method, $limit = 10)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.paymentMethod = :method')
           ->setParameter('method', $method)
           ->orderBy('p.dateAdded', 'DESC');
        
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStatistics($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        
        $qb = $this->createQueryBuilder('p');
        $qb->select(
            'SUM(p.amount) as totalAmount',
            'SUM(p.appliedToCurrent) as totalAppliedToCurrent',
            'SUM(p.appliedToArrears) as totalAppliedToArrears',
            'COUNT(p.id) as paymentCount'
        )
        ->where('p.year = :year')
        ->andWhere('p.status = :status')
        ->setParameter('year', $year)
        ->setParameter('status', 'completed');
        
        $result = $qb->getQuery()->getOneOrNullResult();
        
        if ($result) {
            return [
                'totalAmount' => (float)$result['totalAmount'],
                'totalAppliedToCurrent' => (float)$result['totalAppliedToCurrent'],
                'totalAppliedToArrears' => (float)$result['totalAppliedToArrears'],
                'paymentCount' => (int)$result['paymentCount']
            ];
        }
        
        return [
            'totalAmount' => 0,
            'totalAppliedToCurrent' => 0,
            'totalAppliedToArrears' => 0,
            'paymentCount' => 0
        ];
    }
} 