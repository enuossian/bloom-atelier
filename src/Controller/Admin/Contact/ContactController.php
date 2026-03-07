<?php

namespace App\Controller\Admin\Contact;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/contact', name: 'app_admin_contact_index', methods: ['GET'])]
    public function index(): Response
    {
        $contacts = $this->contactRepository->findAll();

        return $this->render('pages/admin/contact/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    #[Route('/contact/{id<\d+>}/delete', name: 'app_admin_contact_delete', methods: ['POST'])]
    public function delete(Contact $contact, Request $request): Response
    {
        if ($this->isCsrfTokenValid("contact-delete-{$contact->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($contact);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le contact a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_contact_index');
    }
}
