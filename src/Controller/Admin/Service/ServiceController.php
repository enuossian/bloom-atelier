<?php

namespace App\Controller\Admin\Service;

use App\Entity\Service;
use App\Form\Admin\ServiceFormType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ServiceController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ServiceRepository $serviceRepository,
    ) {
    }

    #[Route('/service', name: 'app_admin_service_index', methods: ['GET'])]
    public function index(): Response
    {
        $services = $this->serviceRepository->findAll();

        return $this->render('pages/admin/service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/service/create', name: 'app_admin_service_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response

    {   // On crée une nouvelle instance de Service
        $service = new Service();

        // On crée le formulaire en lui passant l'instance de Service
        $form = $this->createForm(ServiceFormType::class, $service);

        // On traite la requête du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // On définit les dates de création et de mise à jour du service
            $service->setCreatedAt(new \DateTimeImmutable());
            $service->setUpdatedAt(new \DateTimeImmutable());

            // On prépare l'entité pour l'enregistrement en base de données
            $this->entityManager->persist($service);
            // On flush pour exécuter la requête d'insertion en base de données
            $this->entityManager->flush();

            // On génère un message flash de succès
            $this->addFlash('success', 'Le service a été ajouté avec succès.');

            // On redirige l'admin vers la page d'index des services
            return $this->redirectToRoute('app_admin_service_index');
        }

        // Si formulaire non soumis ou non valide, on affiche la page de création du service avec le formulaire
        return $this->render('pages/admin/service/create.html.twig', [
            'serviceForm' => $form,
        ]);
    }

    #[Route('/service/{id<\d+>}/edit', name: 'app_admin_service_edit', methods: ['GET', 'POST'])]
    public function edit(Service $service, Request $request): Response
    {
        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($service);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le service a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_service_index');
        }

        return $this->render('pages/admin/service/edit.html.twig', [
            'serviceForm' => $form,
            'service' => $service,
        ]);
    }

    #[Route('/service/{id<\d+>}/delete', name: 'app_admin_service_delete', methods: ['POST'])]
    public function delete(Service $service, Request $request): Response
    {
        if ($this->isCsrfTokenValid("delete-service-{$service->getId()}", $request->request->get('csrf_token'))) {
            foreach ($service->getSessions() as $session) {
                if (!$session->getBookItems()->isEmpty()) {
                    $this->addFlash('danger', 'Vous ne pouvez pas supprimer ce service car il possède des réservations.');

                    return $this->redirectToRoute('app_admin_service_index');
                }
            }
            $this->entityManager->remove($service);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le service a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_service_index');
    }

    #[Route('/service/{id<\d+>}/active', name: 'app_admin_service_active', methods: ['POST'])]
    public function active(Service $service, Request $request): Response
    {
        // si le token n'est pas valide on redirige vers admin service index
        if (!$this->isCsrfTokenValid("active-service-{$service->getId()}", $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_admin_service_index');
        }

        // Si le service est inactif
        if (!$service->isActive()) {
            // On l'active
            $service->setIsActive(true);

            // On génère le message flash
            $this->addFlash('success', 'Le service est actif.');
        } else {
            // Si le service est actif,
            // On le désactive
            $service->setIsActive(false);

            // On génère le message flash
            $this->addFlash('success', 'Le service est inactif.');
        }

        // On sauvegarde les modifications en bdd

        $this->entityManager->persist($service);
        $this->entityManager->flush();

        // On redirige l'admin vers admin service index
        return $this->redirectToRoute('app_admin_service_index');
    }
}
