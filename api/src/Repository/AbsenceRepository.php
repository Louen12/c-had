<?php

namespace App\Repository;

use App\Entity\Absence;
use App\Entity\Stall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Absence>
 */
class AbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    /**
     * @return Absence[]
     */
    public function findByStallAndDate(Stall $stall, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.stall = :stall')
            ->andWhere('a.date = :date')
            ->setParameter('stall', $stall)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }
}
