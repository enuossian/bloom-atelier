<?php

namespace App\Controller\Visitor\App;

use App\Repository\CommentRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly CommentRepository $commentRepository,
    ) {
    }

    #[Route('/', name: 'visitor_home_index')]
    public function index(): Response
    {
        // récupète tous les services actifs
        $services = $this->serviceRepository->findBy(['isActive' => true]);

        // récupère 3 commentaires visibles
        $comments = $this->commentRepository->findBy(['isVisible' => true], ['createdAt' => 'DESC'], 3);

        return $this->render('pages/visitor/app/index.html.twig', [
            'services' => $services,
            'comments' => $comments,
        ]);
    }

    #[Route('/a-propos', name: 'visitor_home_about_us')]
    public function about_us(): Response
    {
        return $this->render('pages/visitor/app/about_us.html.twig');
    }

    #[Route('/cgv', name: 'visitor_home_cgv')]
    public function cgv(): Response
    {
        return $this->render('pages/visitor/app/terms.html.twig');
    }

    #[Route('/mentions-legales', name: 'visitor_home_mentions')]
    public function mentions(): Response
    {
        return $this->render('pages/visitor/app/legal.html.twig');
    }

    #[Route('/politique-de-confidentialite', name: 'visitor_home_privacy')]
    public function privacy(): Response
    {
        return $this->render('pages/visitor/app/privacy.html.twig');
    }
}
