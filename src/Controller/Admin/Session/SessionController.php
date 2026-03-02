<?php

namespace App\Controller\Admin\Session;

use App\Entity\Session;
use App\Form\Admin\SessionFormType;
use App\Repository\ServiceRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class SessionController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly SessionRepository $sessionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/session', name: 'app_admin_session_index', methods: ['GET'])]
    public function index(): Response
    {
        $sessions = $this->sessionRepository->findAll();

        return $this->render('pages/admin/session/index.html.twig', [
            'sessions' => $sessions,
        ]);
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
            $session->setCreatedAt(new \DateTimeImmutable());
            $session->setUpdatedAt(new \DateTimeImmutable());

            // à corriger !!!
            $session->setReference(bin2hex(random_bytes(8)));

            $this->entityManager->persist($session);
            $this->entityManager->flush();

            $this->addFlash('success', 'La session a été créée avec succès.');

            return $this->redirectToRoute('app_admin_session_index');
        }

        return $this->render('pages/admin/session/create.html.twig', [
            'sessionForm' => $form,
        ]);
    }

    #[Route('/session/show/{id<\d+>}', name: 'app_admin_session_show', methods: ['GET'])]
    public function show(Session $session): Response
    {
        return $this->render('/pages/admin/session/show.html.twig', [
            'session' => $session,
        ]);
    }

    #[Route('/session/edit/{id<\d+>}', name: 'app_admin_session_edit', methods: ['GET', 'POST'])]
    public function edit(Session $session, Request $request): Response
    {
        $form = $this->createForm(SessionFormType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($session);
            $this->entityManager->flush();

            $this->addFlash('success', 'La session a été modifiée avec succès.');

            return $this->redirectToRoute('app_admin_session_index');
        }

        return $this->render('/pages/admin/session/edit.html.twig', [
            'session' => $session,
            'sessionForm' => $form,
        ]);
    }
}
