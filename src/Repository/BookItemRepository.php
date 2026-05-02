<?php

namespace App\Repository;

use App\Entity\BookItem;
use App\Entity\User;
use App\Enum\BookingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookItem>
 */
class BookItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookItem::class);
    }

    /**
     * @return BookItem[] Returns an array of BookItem objects
     */
    public function findUpcomingBookItemsByUser(User $user): array
    {
        return $this->createQueryBuilder('bookItem')
            ->innerJoin('bookItem.session', 's')
            ->innerJoin('bookItem.booking', 'b')
            ->addSelect('s', 'b')
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
     * @return BookItem[] Returns an array of BookItem objects
     */
    public function findPastBookItemsByUser(User $user): array
    {
        return $this->createQueryBuilder('bookItem')
            ->innerJoin('bookItem.session', 's')
            ->innerJoin('bookItem.booking', 'b')
            ->addSelect('s', 'b')
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
