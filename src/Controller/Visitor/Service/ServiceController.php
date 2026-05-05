<?php

namespace App\Controller\Visitor\Service;

use App\Entity\Comment;
use App\Entity\Service;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Repository\BookingRepository;
use App\Repository\CommentRepository;
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
        private readonly CommentRepository $commentRepository,
        private readonly BookingRepository $bookingRepository,
    ) {}

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

        // Récupère les sessions disponibles pour un service donné, c'est-à-dire celles qui sont à l'état "Disponible" et dont la date de début est dans le futur
        $sessions = $this->sessionRepository->findAvailableByService($service);

        // Récupérer les 3 derniers commentaires visibles pour ce service
        $comments = $this->commentRepository->findBy(['service' => $service, 'isVisible' => true], ['createdAt' => 'DESC'], 3);

        /**
         * @var User|null $user
         */
        $user = $this->getUser();


        // Si l'utilisateur a réservé le service, créer le formulaire de commentaire

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $hasBookedService = $user && $this->bookingRepository->hasUserBookedService($user, $service);

        if ($hasBookedService) {
            $form->handleRequest($request);

            if ($form->isSubmitted()) {

                if ($form->isValid()) {

                    $comment->setService($service);
                    $comment->setUser($user);
                    $comment->setCreatedAt(new \DateTimeImmutable());

                    $this->entityManager->persist($comment);
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Merci pour votre commentaire ! Il sera visible après validation par notre équipe.');

                    return $this->redirectToRoute('app_visitor_service_show', [
                        'id' => $service->getId(),
                        'slug' => $service->getSlug(),
                    ]);
                } else {
                    $this->addFlash('danger', 'Le formulaire contient des erreurs. Veuillez les corriger et réessayer.');
                }
            } 
        }

        return $this->render('pages/visitor/service/show.html.twig', [
            'service' => $service,
            'sessions' => $sessions,
            'commentForm' => $form,
            'comments' => $comments,
            'hasBookedService' => $hasBookedService,
        ]);
    }
}
