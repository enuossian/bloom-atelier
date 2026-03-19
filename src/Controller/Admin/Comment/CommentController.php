<?php

namespace App\Controller\Admin\Comment;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class CommentController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/comment', name: 'app_admin_comment_index', methods: ['GET'])]
    public function index(): Response
    {
        $comments = $this->commentRepository->findAll();

        return $this->render('pages/admin/comment/index.html.twig', [
            'comments' => $comments,
        ]);
    }

    #[Route('/comment/{id<\d+>}/delete', name: 'app_admin_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, Request $request): Response
    {
        if ($this->isCsrfTokenValid("comment-delete-{$comment->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();

            $this->addFlash('success', "L'avis a été supprimé avec succès.");
        }

        return $this->redirectToRoute('app_admin_comment_index');
    }

    #[Route('/comment/{id<\d+>}/visible', name: 'app_admin_comment_visible', methods: ['POST'])]
    public function visible(Comment $comment, Request $request): Response
    {
        // si le token n'est pas valide on redirige vers admin service index
        if (!$this->isCsrfTokenValid("visible-comment-{$comment->getId()}", $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_admin_comment_index');
        }

        // Si le commentaire est inactif
        if (!$comment->isVisible()) {
            // On le rend visible
            $comment->setIsVisible(true);

            // On génère le message flash
            $this->addFlash('success', 'Le commentaire est visible.');
        } else {
            // Si le commentaire est actif,
            // On le masque
            $comment->setIsVisible(false);

            // On génère le message flash
            $this->addFlash('success', 'Le commentaire est masqué.');
        }

        // On sauvegarde les modifications en bdd

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        // On redirige l'admin vers admin comment index
        return $this->redirectToRoute('app_admin_comment_index');
    }
}
