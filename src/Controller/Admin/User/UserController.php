<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Form\Admin\EditUserRolesFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    #[Route('/user', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('pages/admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id<\d+>}/edit-roles', name: 'app_admin_user_edit_roles', methods: ['GET', 'POST'])]
    public function editRoles(User $user, Request $request): Response
    {
        // éviter de passer par l'url pour modifier le rôle super admin
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('app_admin_user_index');
        }

        $form = $this->createForm(EditUserRolesFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', "Le rôle de l'utilisateur a été modifié avec succès.");

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('pages/admin/user/edit_roles.html.twig', [
            'user' => $user,
            'userRolesForm' => $form,
        ]);
    }

    #[Route('/user/{id<\d+>}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(User $user, Request $request): Response
    {
        // sécurité supplémentaire empechant de passer par l'url afin de supprimer le super admin
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('app_admin_user_index');
        }

        // empêcher la suppression d'un utilisateur possédant des réservations
        if (!$user->getBookings()->isEmpty()) {
            $this->addFlash('danger', "Impossible de supprimer l'utilisateur {$user->getFirstName()} {$user->getLastName()} car il possède des réservations.");
            return $this->redirectToRoute('app_admin_user_index');
        }

        if ($this->isCsrfTokenValid("delete-user-{$user->getId()}", $request->request->get('csrf_token'))) {
            // message avant la suppression en bdd pour disposer des données de l'utilisateur
            $this->addFlash('success', "L'utilisateur {$user->getFirstName()} {$user->getLastName()} a été supprimé avec succès.");

            if ($this->getUser() == $user) {
                $this->container->get('security.token_storage')->setToken(null);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_user_index');
    }
}
