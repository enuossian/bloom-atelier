<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\BookingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function hasUserBookedService(User $user, Service $service): bool
    {
        return (bool) $this->createQueryBuilder('b')
            ->join('b.bookItems', 'bi')
            ->join('bi.session', 's')
            ->where('b.user = :user')
            ->andWhere('b.status = :status')
            ->andWhere('s.service = :service')
            ->setParameter('user', $user)
            ->setParameter('status', BookingStatus::Paid)
            ->setParameter('service', $service)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
