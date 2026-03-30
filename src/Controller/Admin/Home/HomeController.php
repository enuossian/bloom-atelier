<?php

namespace App\Controller\Admin\Home;

use App\Repository\BookingRepository;
use App\Repository\CommentRepository;
use App\Repository\ContactRepository;
use App\Repository\ServiceRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly SessionRepository $sessionRepository,
        private readonly CommentRepository $commentRepository,
        private readonly ContactRepository $contactRepository,
        private readonly BookingRepository $bookingRepository,
    ) {
    }

    #[Route('/', name: 'app_admin_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/home/index.html.twig', [
            'users_counted' => $this->userRepository->count(),
            'services_counted' => $this->serviceRepository->count(),
            'sessions_counted' => $this->sessionRepository->count(),
            'comments_counted' => $this->commentRepository->count(),
            'contacts_counted' => $this->contactRepository->count(),
            'bookings_counted' => $this->bookingRepository->count(),
        ]);
    }
}
