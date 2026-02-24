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
    {
        $service = new Service();

        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->setCreatedAt(new \DateTimeImmutable());
            $service->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($service);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le service a été ajouté avec succès.');

            return $this->redirectToRoute('app_admin_service_index');
        }

        return $this->render('pages/admin/service/create.html.twig', [
            'serviceForm' => $form,
        ]);
    }

    #[Route('/service/edit/{id<\d+>}', name: 'app_admin_service_edit', methods: ['GET', 'POST'])]
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
        ]);
    }

    #[Route('/service/delete/{id<\d+>}', name: 'app_admin_service_delete', methods: ['POST'])]
    public function delete(Service $service, Request $request): Response
    {
        if ($this->isCsrfTokenValid("service-{$service->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($service);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le service a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_service_index');
    }
}
