<?php

namespace App\Controller\Admin\Service;

use App\Entity\Service;
use App\Form\Admin\ServiceFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/service/create', name: 'app_admin_service_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();

        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->setCreatedAt(new \DateTimeImmutable());
            $service->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', 'Le service a été ajouté avec succès.');

            return $this->redirectToRoute('app_admin_service_index');
        }

        return $this->render('pages/admin/service/create.html.twig', [
            'serviceForm' => $form,
        ]);
    }
}
