<?php

namespace App\Controller\Visitor\SiteMap;

use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SiteMapController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
    ) {
    }

    #[Route('/sitemap.xml', name: 'app_visitor_sitemap_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $hostName = $request->getSchemeAndHttpHost();

        // Initialiser un tableau pour stocker les URLs du sitemap
        $urls = [];

        // Ajouter à la liste des URLs la page d'accueil
        $urls[] = [
            'loc' => $this->generateUrl('visitor_home_index'),
        ];

        // Récupérer les services actifs pour les ajouter au sitemap
        $services = $this->serviceRepository->findBy(['isActive' => true]);

        // Utiliser une boucle pour parcourir chaque service et l'ajouter à la liste des URLs du sitemap
        foreach ($services as $service) {
            $urls[] = [
                'loc' => $this->generateUrl('app_visitor_service_show', ['id' => $service->getId(), 'slug' => $service->getSlug()]),
                'lastmod' => $service->getUpdatedAt()->format('Y-m-d'),
            ];
        }

        $response = $this->render('pages/visitor/site_map/index.html.twig', [
            'host_name' => $hostName,
            'urls' => $urls,
        ]);

        // Définir l'en-tête Content-Type pour indiquer que la réponse est au format XML
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
