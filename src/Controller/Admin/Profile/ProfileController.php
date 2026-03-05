<?php

namespace App\Controller\Admin\Profile;

use App\Entity\User;
use App\Form\EditPasswordProfileFormType;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManagerInterface,
        private readonly UserPasswordHasherInterface $hasher,
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

    #[Route('/profile/password-edit', name: 'app_admin_profile_password_edit', methods: ['GET', 'POST'])]
    public function editPassword(Request $request): Response
    {
        // on n'associe pas le formulaire à l'entité user, car le mdp est stocké en bdd de manière hachée, on ne doit pas récupérer le haché pour l'afficher à l'admin

        $form = $this->createForm(EditPasswordProfileFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // on récupère l'utilisateur courant dans $admin
            /** On précie que $admin est un objet de l'entité User.
             * @var User
             */
            $admin = $this->getUser();

            $admin->setUpdatedAt(new \DateTimeImmutable());

            // encodage du mot de passe
            $formData = $form->getData();
            $hashedPassword = $this->hasher->hashPassword($admin, $formData['plainPassword']);
            // formData est un tableau contenant currentPassword et plainPassword
            $admin->setPassword($hashedPassword);

            $this->entityManagerInterface->persist($admin);
            $this->entityManagerInterface->flush();

            $this->addFlash('success', 'Le mot de passe a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_profile_index');
        }

        return $this->render('pages/admin/profile/edit_password.html.twig', [
            'passwordProfileForm' => $form,
        ]);
    }
}
