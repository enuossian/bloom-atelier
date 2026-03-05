<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\BookItem;
use App\Entity\Service;
use App\Entity\Session;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Enum\SessionStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BookingFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->createUser();
        $manager->persist($user);

        $service = $this->createService();
        $manager->persist($service);

        $session = $this->createSession($service);
        $manager->persist($session);

        $booking = $this->createBooking($user, $service);
        $manager->persist($booking);

        $bookItem = $this->createBookItem($booking, $session);
        $manager->persist($bookItem);

        $manager->flush();
    }

    private function createService(): Service
    {
        $service = new Service();

        $service->setName('Coaching individuel');
        $service->setPrice('180.00');
        $service->setDuration(90);
        $service->setDescription('Séance de coaching individuel.');
        $service->setIsActive(true);
        $service->setCreatedAt(new \DateTimeImmutable());

        return $service;
    }

    private function createSession(Service $service): Session
    {
        $session = new Session();

        $startTime = new \DateTimeImmutable('+1 month');
        $endTime = $startTime->modify('+90 minutes');

        $session->setService($service);
        $session->setReference(bin2hex(random_bytes(8)));
        $session->setStartTime($startTime);
        $session->setEndTime($endTime);
        $session->setMaxParticipants(3);
        $session->setStatus(SessionStatus::Available);
        $session->setCreatedAt(new \DateTimeImmutable());

        return $session;
    }

    private function createBooking(User $user, Service $service): Booking
    {
        $booking = new Booking();

        $booking->setReference(bin2hex(random_bytes(8)));
        $booking->setTotalAmount($service->getPrice());
        $booking->setStatus(BookingStatus::Pending);
        $booking->setConfirmationSent(false);
        $booking->setCreatedAt(new \DateTimeImmutable());
        $booking->setUpdatedAt(new \DateTimeImmutable());
        $booking->setUser($user);

        return $booking;
    }

    private function createBookItem(Booking $booking, Session $session): BookItem
    {
        $bookItem = new BookItem();

        $bookItem->setBooking($booking);
        $bookItem->setSession($session);
        $bookItem->setPrice($session->getService()->getPrice());
        $bookItem->setCreatedAt(new \DateTimeImmutable());

        return $bookItem;
    }

    private function createUser(): User
    {
        $user = new User();

        $user->setFirstName('Marie');
        $user->setLastName('Dupont');
        $user->setEmail('marie.dupont@live.fr');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, 'azerty1234A*'));
        $user->setIsVerified(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setVerifiedAt(new \DateTimeImmutable());

        return $user;
    }
}
