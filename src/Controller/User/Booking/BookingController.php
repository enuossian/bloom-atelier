<?php

namespace App\Controller\User\Booking;

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

    #[Route('/booking/create/{id<\d+>}', name: 'app_user_booking_create', methods: ['POST'])]
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
            'user' => $user,
            'status' => BookingStatus::Pending,
        ]);

        // Vérifier que la session n'est pas déjà dans le panier
        foreach ($booking->getBookItems() as $item) {
            if ($item->getSession() === $session) {
                $this->addFlash('warning', 'Cette session est déjà dans votre panier.');

                return $this->redirectToRoute('app_visitor_service_show', [
                    'id' => $session->getService()->getId(),
                    'slug' => $session->getService()->getSlug(),
                ]);
            }
        }

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

    #[Route('/booking', name: 'app_user_booking_index', methods: ['GET'])]
    public function index(): Response
    {
        // Récupérer le panier en cours
        $booking = $this->bookingRepository->findOneBy([
            'user' => $this->getUser(),
            'status' => BookingStatus::Pending,
        ]);

        return $this->render('pages/user/booking/index.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/booking/remove/{id}', name: 'app_user_booking_remove_item', methods: ['POST'])]
    public function removeItem(BookItem $bookItem, Request $request): Response
    {
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid("booking-remove-{$bookItem->getId()}", $request->request->get('csrf_token'))) {
            // Récupérer le panier en cours
            $booking = $bookItem->getBooking();

            // Supprimer le bookItem du panier (bookItem sera automatiquement supprimé de la base de données grâce à l'option "orphanRemoval=true" dans l'entité Booking)
            $booking->removeBookItem($bookItem);

            // Supprimer le booking s'il ne contient plus de bookItem et éviter d'avoir des paniers vides dans la base de données
            if ($booking->getBookItems()->isEmpty()) {
                $this->entityManager->remove($booking);
            } else {
                // Recalculer le montant total du panier
                $booking->setTotalAmount($booking->calculateTotalAmount());
                // Mettre à jour la date de modification du panier
                $booking->setUpdatedAt(new \DateTimeImmutable());
            }

            // Executer les requêtes en base de données
            $this->entityManager->flush();

            // Afficher un message de succès
            $this->addFlash('success', 'La session a été supprimée du panier.');
        }

        return $this->redirectToRoute('app_user_booking_index');
    }
}
