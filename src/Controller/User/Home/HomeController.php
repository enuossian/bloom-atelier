<?php

namespace App\Controller\User\Home;

use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class HomeController extends AbstractController
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
    ) {
    }

    #[Route('/', name: 'app_user_home', methods: ['GET'])]
    public function index(): Response
    {
        // récupérer l'utilisateur courant
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // récupérer les sessions fututres de l'utilisateur
        $upcomingSessions = $this->sessionRepository->findUpcomingSessionsByUser($user);

        // récupérer les sessions passées de l'utilisateur
        $passedSessions = $this->sessionRepository->findPastSessionsByUser($user);

        return $this->render('pages/user/home/index.html.twig', [
            'passedSessions' => $passedSessions,
            'upcomingSessions' => $upcomingSessions,
            'passedSessionsCounted' => count($passedSessions),
            'upcomingSessionsCounted' => count($upcomingSessions),
        ]);
    }
}
