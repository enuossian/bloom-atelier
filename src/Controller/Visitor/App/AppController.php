<?php

namespace App\Controller\Visitor\App;

use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
    ) {
    }

    #[Route('/', name: 'visitor_home_index')]
    public function index(): Response
    {
        $services = $this->serviceRepository->findAll();

        return $this->render('pages/visitor/app/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/notre-concept', name: 'visitor_home_concept')]
    public function concept(): Response
    {
        return $this->render('pages/visitor/app/concept.html.twig');
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
}
