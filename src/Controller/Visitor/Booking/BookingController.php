<?php

namespace App\Controller\Visitor\Booking;

use App\Entity\Booking;
use App\Entity\BookItem;
use App\Entity\Session;
use App\Enum\BookingStatus;
use App\Enum\SessionStatus;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('user')]
final class BookingController extends AbstractController
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/booking/create/{id<\d+>}', name: 'app_visitor_booking_create', methods: ['POST'])]
    public function create(Session $session, Request $request): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('booking-create'.$session->getId(), $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_visitor_service_show', [
                'id' => $session->getService()->getId(),
                'slug' => $session->getService()->getSlug(),
            ]);
        }

        // Vérifier si l'utilisateur est connecté

        /**
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter avant de réserver une session.');

            return $this->redirectToRoute('app_login');
        }

        // Vérifier que la session est disponible
        if (SessionStatus::Available !== $session->getStatus()) {
            $this->addFlash('danger', "Cette session n'est plus disponible à la réservation.");

            return $this->redirectToRoute('app_visitor_service_show', [
                'id' => $session->getService()->getId(),
                'slug' => $session->getService()->getSlug(),
            ]);
        }

        // Récupérer le panier en cours

        $booking = $this->bookingRepository->findOneBy([
            'status' => BookingStatus::Pending,
            'user' => $user,
        ]);

        // Sinon créer une nouvelle réservation
        if (!$booking) {
            $booking = new Booking();

            $booking->setUser($user);
            $booking->setReference('BOOK-'.strtoupper(bin2hex(random_bytes(4))));
            $booking->setCreatedAt(new \DateTimeImmutable());
            $booking->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($booking);
        }

        // Créer le bookItem

        $bookItem = new BookItem();

        $bookItem->setBooking($booking);
        $bookItem->setSession($session);
        $bookItem->setPrice($session->getService()->getPrice());
        $bookItem->setCreatedAt(new \DateTimeImmutable());
        $booking->addBookItem($bookItem);

        $this->entityManager->persist($bookItem);

        $booking->setTotalAmount($booking->calculateTotalAmount());

        $this->entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été ajoutée au panier.');

        return $this->redirectToRoute('app_visitor_service_show', [
            'id' => $session->getService()->getId(),
            'slug' => $session->getService()->getSlug(),
        ]);
    }
}
