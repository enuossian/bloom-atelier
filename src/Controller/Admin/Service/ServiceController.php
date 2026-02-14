<?php

namespace App\Controller\Admin\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ServiceController extends AbstractController
{
    #[Route('/service', name: 'app_admin_service_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/service/index.html.twig');
    }
    #[Route('/service/create', name: 'app_admin_service_create', methods: ['GET'])]
    public function create(): Response
    {
        //$service = new Service();
        return $this->render('pages/admin/service/create.html.twig');
    }
}
