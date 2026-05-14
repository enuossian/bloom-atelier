<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        private readonly Environment $twig,
    ) {
    }

    public function onRequestEvent(RequestEvent $event): void
    {
        $maintenanceFile = $this->projectDir.'/var/maintenance.lock';

        // Si le fichier de maintenance n'existe pas, on ne fait rien
        if (!file_exists($maintenanceFile)) {
            return;
        }

        // Sinon, on affiche la page de maintenance
        $template = $this->twig->render('pages/maintenance_mode/index.html.twig');

        // On crée une réponse avec la page de maintenance et le code HTTP 503 Service Unavailable
        $response = new Response($template, Response::HTTP_SERVICE_UNAVAILABLE);

        // On définit la réponse dans l'événement
        $event->setResponse($response);

        // On arrête la propagation de l'événement
        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequestEvent',
        ];
    }
}
