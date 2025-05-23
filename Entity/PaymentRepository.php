<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

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
        
        try {
            $result = $qb->getQuery()->getSingleScalarResult();
            return $result ? $result : 0;
        } catch (\Exception $e) {
            return 0;
        }
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
        // Default response with zero values
        $defaultStats = [
            'totalAmount' => 0,
            'totalAppliedToCurrent' => 0,
            'totalAppliedToArrears' => 0,
            'paymentCount' => 0
        ];
        
        // Handle null values
        if ($year === null) {
            $year = date('Y');
        }
        
        try {
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
                    'totalAmount' => (float)($result['totalAmount'] ?? 0),
                    'totalAppliedToCurrent' => (float)($result['totalAppliedToCurrent'] ?? 0),
                    'totalAppliedToArrears' => (float)($result['totalAppliedToArrears'] ?? 0),
                    'paymentCount' => (int)($result['paymentCount'] ?? 0)
                ];
            }
            
            return $defaultStats;
        } catch (\Exception $e) {
            // Return default values in case of error
            return $defaultStats;
        }
    }
} 