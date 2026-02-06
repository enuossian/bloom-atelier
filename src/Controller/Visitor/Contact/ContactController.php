<?php

namespace App\Controller\Visitor\Contact;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'visitor_contact')]
final class ContactController extends AbstractController
{
    #[Route('/contact', name: '_index')]
    public function index(): Response
    {
        return $this->render('pages/visitor/contact/index.html.twig');
    }
}
