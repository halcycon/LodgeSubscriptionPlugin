// Entity/SubscriptionRateRepository.php
<?php
namespace MauticPlugin\LodgeSubscriptionPlugin\Entity;

use Doctrine\ORM\EntityRepository;

class SubscriptionRateRepository extends EntityRepository
{
    /**
     * Get paginated rates
     */
    public function getRates($start = 0, $limit = 10, $search = null)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->orderBy('r.year', 'DESC');
        
        if ($search) {
            $qb->andWhere(
                $qb->expr()->like('r.description', ':search')
            )->setParameter('search', '%' . $search . '%');
        }

        if ($limit > 0) {
            $qb->setFirstResult($start)
               ->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
    
    /**
     * Count rates
     */
    public function countRates($search = null)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('COUNT(r.id)');
        
        if ($search) {
            $qb->andWhere(
                $qb->expr()->like('r.description', ':search')
            )->setParameter('search', '%' . $search . '%');
        }
        
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get rate for specific year
     */
    public function getRateForYear($year)
    {
        return $this->findOneBy(['year' => $year]);
    }

    /**
     * Get all rates ordered by year
     */
    public function getAllRates()
    {
        $qb = $this->createQueryBuilder('r');
        $qb->orderBy('r.year', 'DESC');
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Check if rate exists for year
     */
    public function rateExistsForYear($year)
    {
        $rate = $this->findOneBy(['year' => $year]);
        return $rate !== null;
    }
}