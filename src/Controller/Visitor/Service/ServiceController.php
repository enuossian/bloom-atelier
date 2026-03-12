<?php

namespace App\Controller\Visitor\Service;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly SessionRepository $sessionRepository,
    ) {
    }

    #[Route('/services', name: 'app_visitor_service_index', methods: ['GET'])]
    public function index(): Response
    {
        $services = $this->serviceRepository->findBy(['isActive' => true]);

        return $this->render('pages/visitor/service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/service/{id<\d+>}/{slug}', name: 'app_visitor_service_show', methods: ['GET'])]
    public function showService(Service $service): Response
    {
        $sessions = $this->sessionRepository->findAvailableByService($service);

        return $this->render('pages/visitor/service/show.html.twig', [
            'service' => $service,
            'sessions' => $sessions,
        ]);
    }
}
