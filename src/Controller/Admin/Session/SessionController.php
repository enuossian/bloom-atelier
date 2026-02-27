<?php

namespace App\Controller\Admin\Session;

use App\Entity\Session;
use App\Form\Admin\SessionFormType;
use App\Repository\ServiceRepository;
//use App\Repository\SessionRepository;
// use DateTimeImmutable;
//use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class SessionController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        //private readonly SessionRepository $sessionRepository,
        //private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/session', name: 'app_admin_session_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/session/index.html.twig');
    }

    #[Route('/session/create', name: 'app_admin_session_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        // vérifie qu'un service existe avant d'accéder à la création d'une session
        if (0 == $this->serviceRepository->count()) {
            $this->addFlash('warning', 'Vous devez créer au moins un service avant de créer une session.');

            return $this->redirectToRoute('app_admin_service_index');
        }

        $session = new Session();

        $form = $this->createForm(SessionFormType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd('continue');
            /* $session->setCreatedAt(new DateTimeImmutable());
            $this->entityManager->persist($session);
            $this->entityManager->flush(); */
        }

        return $this->render('pages/admin/session/create.html.twig', [
            'sessionForm' => $form,
        ]);
    }
}
