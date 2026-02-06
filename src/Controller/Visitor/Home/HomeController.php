<?php

namespace App\Controller\Visitor\Home;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'visitor_home')]
final class HomeController extends AbstractController
{
    #[Route('/', name: '_index')]
    public function index(): Response
    {
        return $this->render('pages/visitor/home/index.html.twig');
    }

    #[Route('/notre-concept', name: '_concept')]
    public function concept(): Response
    {
        return $this->render('pages/visitor/home/concept.html.twig');
    }

    #[Route('/nos-prestations', name: '_prestations')]
    public function services(): Response
    {
        return $this->render('pages/visitor/home/services.html.twig');
    }

    #[Route('/cgv', name: '_cgv')]
    public function cgv(): Response
    {
        return $this->render('pages/visitor/home/terms.html.twig');
    }

    #[Route('/mentions-legales', name: '_mentions')]
    public function mentions(): Response
    {
        return $this->render('pages/visitor/home/legal.html.twig');
    }
}
