<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\Session;
use App\Enum\SessionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * @return Session[] returns an array of Session objects
     */
    public function findAvailableByService(Service $service): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.service = :service')
            ->andWhere('s.status = :status')
            ->andWhere('s.startTime > :now')
            ->setParameter('service', $service)
            ->setParameter('status', SessionStatus::Available)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
