<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Service;
use App\Entity\Session;
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

    // Vérifie si une session est déjà présente dans le booking (panier ou payé) d'un utilisateur donné.
    // Empêche les doublons dans le panier et les réservations multiples pour la même session

    public function isSessionInUserBookings(User $user, Session $session): bool
    {
        return null !== $this->createQueryBuilder('b')
            ->join('b.bookItems', 'bi')
            ->where('b.user = :user')
            ->andWhere('bi.session = :session')
            ->andWhere('b.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('session', $session)
            ->setParameter('statuses', [BookingStatus::Pending, BookingStatus::Paid])
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Vérifie si un utilisateur a déjà réservé (payé) un service donné
    // Utile pour autoriser ou non la création d'un commentaire sur un service

    public function hasUserPurchasedService(User $user, Service $service): bool
    {
        return null !== $this->createQueryBuilder('b')
            ->join('b.bookItems', 'bi')
            ->join('bi.session', 's')
            ->where('b.user = :user')
            ->andWhere('s.service = :service')
            ->andWhere('b.status = :status')
            ->setParameter('user', $user)
            ->setParameter('service', $service)
            ->setParameter('status', BookingStatus::Paid)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
