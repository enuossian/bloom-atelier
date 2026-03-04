<?php

namespace App\Controller\Admin\Profile;

use App\Entity\User;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManagerInterface,
    ) {
    }

    #[Route('/profile', name: 'app_admin_profile_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/profile/index.html.twig');
    }

    #[Route('/profile/edit', name: 'app_admin_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        /**
         * @var User
         */
        $admin = $this->getUser();

        // rafraîchit l'entité depuis la base pour éviter les effets de bord
        $this->entityManagerInterface->refresh($admin);

        $form = $this->createForm(ProfileFormType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $admin->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManagerInterface->persist($admin);
            $this->entityManagerInterface->flush();

            $this->addFlash('success', 'Le profil a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_profile_index');
        }

        return $this->render('pages/admin/profile/edit.html.twig', [
            'profileForm' => $form,
        ]);
    }
}
