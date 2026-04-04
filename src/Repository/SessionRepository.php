<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\Session;
use App\Entity\User;
use App\Enum\BookingStatus;
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
     *                   retourne les sessions disponibles pour un service donné, c'est-à-dire celles qui sont à l'état "Disponible" et dont la date de début est dans le futur
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

    /**
     * @return Session[] Returns an array of Session objects
     */
    public function findUpcomingSessionsByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.bookItems', 'bi')
            ->join('bi.booking', 'b')
            ->addSelect('bi', 'b')
            ->andWhere('b.user = :user')
            ->andWhere('b.status = :bookingStatus')
            ->andWhere('s.startTime > :now')
            ->setParameter('user', $user)
            ->setParameter('bookingStatus', BookingStatus::Paid)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Session[] Returns an array of Session objects
     */
    public function findPastSessionsByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.bookItems', 'bi')
            ->join('bi.booking', 'b')
            ->addSelect('bi', 'b')
            ->andWhere('b.user = :user')
            ->andWhere('b.status = :bookingStatus')
            ->andWhere('s.startTime <= :now')
            ->setParameter('user', $user)
            ->setParameter('bookingStatus', BookingStatus::Paid)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.startTime', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
