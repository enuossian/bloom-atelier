<?php

namespace App\Controller\Visitor\App;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'visitor_home_index')]
    public function index(): Response
    {
        return $this->render('pages/visitor/app/index.html.twig');
    }

    #[Route('/notre-concept', name: 'visitor_home_concept')]
    public function concept(): Response
    {
        return $this->render('pages/visitor/app/concept.html.twig');
    }

    #[Route('/nos-prestations', name: 'visitor_home_prestations')]
    public function services(): Response
    {
        return $this->render('pages/visitor/app/services.html.twig');
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
