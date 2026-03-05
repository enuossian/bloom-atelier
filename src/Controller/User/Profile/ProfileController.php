<?php

namespace App\Controller\User\Profile;

use App\Entity\User;
use App\Form\EditPasswordProfileFormType;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    #[Route('/profile', name: 'app_user_profile_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/user/profile/index.html.twig');
    }

    #[Route('/profile/edit', name: 'app_user_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le profil a été modifié avec succès.');

            return $this->redirectToRoute('app_user_profile_index');
        }

        return $this->render('pages/user/profile/edit.html.twig', [
            'profileForm' => $form,
        ]);
    }

    #[Route('/profile/password-edit', name: 'app_user_profile_password_edit', methods: ['GET', 'POST'])]
    public function editPassword(Request $request): Response
    {
        $form = $this->createForm(EditPasswordProfileFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var User
             */
            $user = $this->getUser();
            $user->setUpdatedAt(new \DateTimeImmutable());

            $formData = $form->getData();
            $hashedPassword = $this->hasher->hashPassword($user, $formData['plainPassword']);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le mot de passe a été modifié avec succès.');

            return $this->redirectToRoute('app_user_profile_index');
        }

        return $this->render('pages/user/profile/edit_password.html.twig', [
            'passwordProfileForm' => $form,
        ]);
    }
}
