<?php

namespace App\Controller\User\Home;

use App\Repository\BookItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class HomeController extends AbstractController
{
    public function __construct(
        private readonly BookItemRepository $bookItemRepository,
    ) {
    }

    #[Route('/', name: 'app_user_home', methods: ['GET'])]
    public function index(): Response
    {
        // récupérer l'utilisateur courant
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // récupérer les bookItems futurs de l'utilisateur
        $upcomingBookItems = $this->bookItemRepository->findUpcomingBookItemsByUser($user);

        // récupérer les bookItems passés de l'utilisateur
        $passedBookItems = $this->bookItemRepository->findPastBookItemsByUser($user);

        return $this->render('pages/user/home/index.html.twig', [
            'upcomingBookItems' => $upcomingBookItems,
            'passedBookItems' => $passedBookItems,
            'upcomingBookItemsCounted' => count($upcomingBookItems),
            'passedBookItemsCounted' => count($passedBookItems),
        ]);
    }
}
