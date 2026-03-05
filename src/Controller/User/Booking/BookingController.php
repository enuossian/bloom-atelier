<?php

namespace App\Controller\User\Booking;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class BookingController extends AbstractController
{
    #[Route('/booking', name: 'app_user_booking_create')]
    public function index(): Response
    {
        return $this->render('pages/user/booking/index.html.twig');
    }
}
