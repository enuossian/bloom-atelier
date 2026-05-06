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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        if ($booking) {
            foreach ($booking->getBookItems() as $item) {
                if ($item->getSession() === $session) {
                    $this->addFlash('warning', 'Cette session est déjà dans votre panier.');

                    return $this->redirectToRoute('app_visitor_service_show', [
                        'id' => $session->getService()->getId(),
                        'slug' => $session->getService()->getSlug(),
                    ]);
                }
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
        $booking->setUpdatedAt(new \DateTimeImmutable());

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

    #[Route('/booking/remove/{id<\d+>}', name: 'app_user_booking_remove_item', methods: ['POST'])]
    public function removeItem(BookItem $bookItem, Request $request): Response
    {
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid("booking-remove-{$bookItem->getId()}", $request->request->get('csrf_token'))) {
            // Récupérer le panier en cours
            $booking = $bookItem->getBooking();

            // Vérifier que le panier appartient à l'utilisateur connecté
            if ($booking->getUser() !== $this->getUser()) {
                throw $this->createAccessDeniedException();
            }

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

    #[Route('/booking/checkout', name: 'app_user_booking_checkout', methods: ['POST'])]
    public function checkout(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('booking-checkout', $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_user_booking_index');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $booking = $this->bookingRepository->findOneBy([
            'user' => $user,
            'status' => BookingStatus::Pending,
        ]);

        if (!$booking || $booking->getBookItems()->isEmpty()) {
            $this->addFlash('danger', 'Votre panier est vide.');

            return $this->redirectToRoute('app_user_booking_index');
        }

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $lineItems = [];

        foreach ($booking->getBookItems() as $bookItem) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $bookItem->getSession()->getService()->getName(),
                    ],
                    'unit_amount' => (int) ($bookItem->getPrice() * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'metadata' => [
                'booking_id' => (string) $booking->getId(),
                'reference' => (string) $booking->getReference(),
            ],
            'success_url' => rawurldecode($this->generateUrl(
                'app_user_booking_success',
                ['session_id' => '{CHECKOUT_SESSION_ID}'],
                UrlGeneratorInterface::ABSOLUTE_URL
            )),
            'cancel_url' => $this->generateUrl(
                'app_user_booking_cancel',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $this->redirect($session->url, 303);
    }

    #[Route('/booking/success', name: 'app_user_booking_success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            throw $this->createAccessDeniedException();
        }

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        if ('paid' !== $session->payment_status) {
            throw $this->createAccessDeniedException();
        }

        if (!isset($session->metadata->booking_id)) {
            throw $this->createAccessDeniedException();
        }

        $bookingId = $session->metadata->booking_id;

        $booking = $this->bookingRepository->find($bookingId);

        if (!$booking || $booking->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (BookingStatus::Pending === $booking->getStatus()) {
            $booking->setStatus(BookingStatus::Paid);
            $booking->setUpdatedAt(new \DateTimeImmutable());

            foreach ($booking->getBookItems() as $item) {
                $item->getSession()->updateStatus();
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre paiement a été validé. Merci !');
        }

        return $this->render('pages/user/booking/success.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/booking/cancel', name: 'app_user_booking_cancel', methods: ['GET', 'POST'])]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');

        return $this->redirectToRoute('app_user_booking_index');
    }
}
