// Entity/PaymentRepository.php
<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Entity;

use Doctrine\ORM\EntityRepository;

class PaymentRepository extends EntityRepository
{
    public function getContactPayments($contactId, $limit = 10)
    {
        return $this->createQueryBuilder('p')
            ->where('p.contactId = :contactId')
            ->setParameter('contactId', $contactId)
            ->orderBy('p.dateAdded', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getYearPayments($year)
    {
        return $this->createQueryBuilder('p')
            ->where('p.year = :year')
            ->setParameter('year', $year)
            ->orderBy('p.dateAdded', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getPaymentsByStatus($status)
    {
        return $this->findBy(['status' => $status], ['dateAdded' => 'DESC']);
    }

    public function getPaymentsByMethod($method)
    {
        return $this->findBy(['paymentMethod' => $method], ['dateAdded' => 'DESC']);
    }

    public function getTotalPaymentsForYear($year)
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount) as total')
            ->where('p.year = :year')
            ->andWhere('p.status = :status')
            ->setParameter('year', $year)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?: 0;
    }
}