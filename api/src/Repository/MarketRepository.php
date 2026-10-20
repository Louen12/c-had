<?php

namespace App\Repository;

use App\Entity\Market;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Market>
 */
class MarketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Market::class);
    }

    /**
     * @return Market[]
     */
    public function findWithinRadius(float $latitude, float $longitude, float $radiusKm): array
    {
        $radiusMeters = $radiusKm * 1000;

        return $this->createQueryBuilder('m')
            ->where('ST_DWithin(
                ST_MakePoint(m.longitude, m.latitude)::geography,
                ST_MakePoint(:lng, :lat)::geography,
                :radius
            ) = true')
            ->setParameter('lat', $latitude)
            ->setParameter('lng', $longitude)
            ->setParameter('radius', $radiusMeters)
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
