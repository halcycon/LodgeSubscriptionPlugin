// Entity/SubscriptionRateRepository.php
<?php
namespace MauticPlugin\LodgeSubscriptionBundle\Entity;

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
     * Get the subscription rate for a specific year
     *
     * @param int $year
     * @return SubscriptionRate|null
     */
    public function getForYear($year)
    {
        return $this->findOneBy(['year' => $year]);
    }
    
    /**
     * Get all rates ordered by year
     *
     * @param string $orderDirection
     * @return array
     */
    public function getAllRates($orderDirection = 'DESC')
    {
        return $this->findBy([], ['year' => $orderDirection]);
    }
    
    /**
     * Get most recent rate
     *
     * @return SubscriptionRate|null
     */
    public function getMostRecentRate()
    {
        $qb = $this->createQueryBuilder('r');
        $qb->orderBy('r.year', 'DESC')
           ->setMaxResults(1);
        
        return $qb->getQuery()->getOneOrNullResult();
    }
    
    /**
     * Get rates for a range of years
     *
     * @param int $startYear
     * @param int $endYear
     * @return array
     */
    public function getRatesForYearRange($startYear, $endYear)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where('r.year >= :startYear')
           ->andWhere('r.year <= :endYear')
           ->setParameter('startYear', $startYear)
           ->setParameter('endYear', $endYear)
           ->orderBy('r.year', 'ASC');
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Get total number of rates
     *
     * @return int
     */
    public function getCount()
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('COUNT(r.id)');
        
        return (int)$qb->getQuery()->getSingleScalarResult();
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