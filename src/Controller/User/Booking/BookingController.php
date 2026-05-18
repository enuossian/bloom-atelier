<?php

namespace App\Controller\User\Booking;

use App\Entity\Booking;
use App\Entity\BookItem;
use App\Entity\Session;
use App\Enum\BookingStatus;
use App\Enum\SessionStatus;
use App\Repository\BookingRepository;
use App\Service\SendEmailService;
use App\Service\StripeService;
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
        private readonly StripeService $stripeService,
        private readonly SendEmailService $sendEmailService,
    ) {
    }

    /**
     * Affiche le panier en cours de l'utilisateur connecté.
     * Le panier est un Booking avec le statut Pending stocké en base de données.
     */
    #[Route('/booking', name: 'app_user_booking_index', methods: ['GET'])]
    public function index(): Response
    {
        $booking = $this->bookingRepository->findOneBy([
            'user' => $this->getUser(),
            'status' => BookingStatus::Pending,
        ]);

        return $this->render('pages/user/booking/index.html.twig', [
            'booking' => $booking,
        ]);
    }

    /**
     * Ajoute une session au panier de l'utilisateur connecté.
     * Si l'utilisateur n'a pas de panier en cours, un nouveau Booking Pending est créé.
     */
    #[Route('/booking/create/{id<\d+>}', name: 'app_user_booking_create', methods: ['POST'])]
    public function create(Session $session, Request $request): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('booking-create'.$session->getId(), $request->request->get('csrf_token'))) {
            return $this->redirectToService($session);
        }

        // Vérifier si l'utilisateur est connecté
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter avant de réserver une session.');

            return $this->redirectToRoute('app_login');
        }

        // Vérifier que la session est disponible
        if (SessionStatus::Available !== $session->getStatus()) {
            $this->addFlash('danger', "Cette session n'est plus disponible à la réservation.");

            return $this->redirectToService($session);
        }

        // Vérifier que l'utilisateur n'a pas déjà réservé cette session
        // La vérification couvre les statuts Pending (panier) et Paid (réservation confirmée)
        if ($this->bookingRepository->isSessionInUserBookings($user, $session)) {
            $this->addFlash('warning', 'Cette session est déjà dans votre panier ou déjà réservée.');

            return $this->redirectToService($session);
        }

        // Récupérer le panier en cours ou créer un nouveau Booking Pending
        $booking = $this->bookingRepository->findOneBy([
            'user' => $user,
            'status' => BookingStatus::Pending,
        ]);

        if (!$booking) {
            $booking = new Booking();
            $booking->setUser($user);
            $booking->setReference('BOOK-'.strtoupper(bin2hex(random_bytes(4))));
            $booking->setCreatedAt(new \DateTimeImmutable());
            $booking->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($booking);
        }

        // Créer le BookItem et l'ajouter au panier
        $bookItem = new BookItem();
        $bookItem->setBooking($booking);
        $bookItem->setSession($session);
        $bookItem->setPrice($session->getService()->getPrice());
        $bookItem->setCreatedAt(new \DateTimeImmutable());
        $booking->addBookItem($bookItem);

        $this->entityManager->persist($bookItem);

        // Recalculer le montant total et mettre à jour la date de modification
        $booking->setTotalAmount($booking->calculateTotalAmount());
        $booking->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été ajoutée au panier.');

        return $this->redirectToService($session);
    }

    /**
     * Retire un BookItem du panier.
     * Si le panier est vide après suppression, le Booking Pending est également supprimé.
     */
    #[Route('/booking/remove/{id<\d+>}', name: 'app_user_booking_remove_item', methods: ['POST'])]
    public function removeItem(BookItem $bookItem, Request $request): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid("booking-remove-{$bookItem->getId()}", $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_user_booking_index');
        }

        $booking = $bookItem->getBooking();

        // Vérifier que le panier appartient à l'utilisateur connecté
        if ($booking->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Supprimer le BookItem (orphanRemoval=true le supprime automatiquement en BDD)
        // removeBookItem() dissocie également le BookItem de son Booking via setBooking(null)
        $booking->removeBookItem($bookItem);

        // Supprimer le Booking s'il est vide pour éviter des paniers orphelins en BDD
        if ($booking->getBookItems()->isEmpty()) {
            $this->entityManager->flush(); // Nécessaire pour que le BookItem soit supprimé avant de supprimer le Booking
            $this->entityManager->remove($booking);
        } else {
            // Recalculer le montant total et mettre à jour la date de modification
            $booking->setTotalAmount($booking->calculateTotalAmount());
            $booking->setUpdatedAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'La session a été supprimée du panier.');

        return $this->redirectToRoute('app_user_booking_index');
    }

    /**
     * Crée une session de paiement Stripe et redirige l'utilisateur vers la page de paiement.
     * Le code 303 force le navigateur à utiliser GET pour la redirection.
     */
    #[Route('/booking/checkout', name: 'app_user_booking_checkout', methods: ['POST'])]
    public function checkout(Request $request): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('booking-checkout', $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_user_booking_index');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le panier en cours
        $booking = $this->bookingRepository->findOneBy([
            'user' => $user,
            'status' => BookingStatus::Pending,
        ]);

        if (!$booking || $booking->getBookItems()->isEmpty()) {
            $this->addFlash('danger', 'Votre panier est vide.');

            return $this->redirectToRoute('app_user_booking_index');
        }

        // Générer les URLs de retour après paiement
        // {CHECKOUT_SESSION_ID} est un placeholder remplacé automatiquement par Stripe
        $successUrl = rawurldecode($this->generateUrl(
            'app_user_booking_success',
            ['session_id' => '{CHECKOUT_SESSION_ID}'],
            UrlGeneratorInterface::ABSOLUTE_URL
        ));

        $cancelUrl = $this->generateUrl(
            'app_user_booking_cancel',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $url = $this->stripeService->createCheckoutSession($booking, $successUrl, $cancelUrl);

        return $this->redirect($url, 303);
    }

    /**
     * Vérifie le paiement côté Stripe et confirme le Booking en base de données.
     * La vérification est idempotente : si le Booking est déjà Paid, rien ne se passe.
     */
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

        // Récupérer la session Stripe pour vérifier le statut du paiement
        $stripeSession = $this->stripeService->retrieveSession($sessionId);

        if ('paid' !== $stripeSession->payment_status) {
            throw $this->createAccessDeniedException();
        }

        if (!isset($stripeSession->metadata->booking_id)) {
            throw $this->createAccessDeniedException();
        }

        $booking = $this->bookingRepository->find($stripeSession->metadata->booking_id);

        // Vérifier que le Booking existe et appartient à l'utilisateur connecté
        if (!$booking || $booking->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // Confirmer le paiement uniquement si le Booking est encore en attente
        // (idempotence : évite de traiter deux fois si l'utilisateur recharge la page)
        if (BookingStatus::Pending === $booking->getStatus()) {
            $booking->setStatus(BookingStatus::Paid);
            $booking->setUpdatedAt(new \DateTimeImmutable());

            // Mettre à jour le statut de chaque session réservée
            foreach ($booking->getBookItems() as $item) {
                $item->getSession()->updateStatus();
            }

            // Envoyer l'email uniquement s'il n'a pas encore été envoyé
            if (!$booking->isConfirmationSent()) {
                $this->sendEmailService->sendEmail([
                    'sender_email' => 'hello@bloomatelier.site',
                    'sender_full_name' => 'Hawa Diallo',
                    'recipient_email' => $booking->getUser()->getEmail(),
                    'subject' => 'Confirmation de votre réservation',
                    'html_template' => 'emails/booking_confirmation_email.html.twig',
                    'context' => [
                        'booking' => $booking,
                    ],
                ]);

                $booking->setConfirmationSent(true);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre paiement a été validé. Merci !');
        }

        return $this->render('pages/user/booking/success.html.twig', [
            'booking' => $booking,
        ]);
    }

    /**
     * Redirige vers le panier si l'utilisateur annule le paiement sur Stripe.
     */
    #[Route('/booking/cancel', name: 'app_user_booking_cancel', methods: ['GET', 'POST'])]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');

        return $this->redirectToRoute('app_user_booking_index');
    }

    /**
     * Redirige vers la page d'une session de service.
     * Méthode utilitaire pour éviter la répétition dans create().
     */
    private function redirectToService(Session $session): Response
    {
        return $this->redirectToRoute('app_visitor_service_show', [
            'id' => $session->getService()->getId(),
            'slug' => $session->getService()->getSlug(),
        ]);
    }
}
