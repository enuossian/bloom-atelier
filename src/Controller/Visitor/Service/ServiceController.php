<?php

namespace App\Controller\Visitor\Service;

use App\Entity\Comment;
use App\Entity\Service;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Repository\ServiceRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly SessionRepository $sessionRepository,
        private readonly EntityManagerInterface $entityManager,
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

    #[Route('/service/{id<\d+>}/{slug}', name: 'app_visitor_service_show', methods: ['GET', 'POST'])]
    public function showService(Service $service, Request $request): Response
    {
        // Vérifier que le service est actif, sinon afficher une page 404
        if (!$service->isActive()) {
            throw $this->createNotFoundException('Service non trouvé');
        }

        $sessions = $this->sessionRepository->findAvailableByService($service);

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // rediriger l'utilisateur non connecté vers la page de connexion
            if (!$this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('app_login');
            }

            /**
             * @var User
             */
            $user = $this->getUser();

            $comment->setService($service);
            $comment->setUser($user);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->addFlash('success', 'Merci pour votre commentaire ! Il sera visible après validation par nos équipes.');

            return $this->redirectToRoute('app_visitor_service_show', [
                'id' => $service->getId(),
                'slug' => $service->getSlug(),
            ]);
        }

        return $this->render('pages/visitor/service/show.html.twig', [
            'service' => $service,
            'sessions' => $sessions,
            'commentForm' => $form,
        ]);
    }
}
