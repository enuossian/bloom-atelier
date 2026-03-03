<?php

namespace App\Controller\Admin\Booking;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class BookingController extends AbstractController
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
    ) {
    }

    #[Route('/booking', name: 'app_admin_booking_index', methods: ['GET'])]
    public function index(): Response
    {
        $bookings = $this->bookingRepository->findAll();

        return $this->render('pages/admin/booking/index.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('/booking/{id<\d+>}/show', name: 'app_admin_booking_show', methods: ['GET'])]
    public function show(Booking $booking): Response
    {
        return $this->render('pages/admin/booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }
}
