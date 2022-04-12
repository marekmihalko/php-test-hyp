<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Service\CommentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/wipe", name="wipe_comments", methods={"DELETE"})
     */
    public function wipe(CommentService $commentService, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $article = $request->get('article');
        $articleEntity = $entityManager->getRepository(Article::class)->find($article);
        if (!$articleEntity) {
            return new JsonResponse(['message' => 'Article for wipe comments not exist'], Response::HTTP_BAD_REQUEST);
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $commentService->wipeCommentsInArticle($articleEntity);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Comments wiped'], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Forbidden access to wipe comments'], Response::HTTP_FORBIDDEN);
    }

    /**
     * @Route("/comment/", name="create_comment", methods={"POST"})
     */
    public function create(EntityManagerInterface $entityManager, CommentService $commentService, Request $request): JsonResponse
    {
        $missingData = $commentService->getMissingDataForCreateComment($request);
        if (count($missingData))
            return new JsonResponse(['message' => 'Missing data', 'data' => $missingData], Response::HTTP_BAD_REQUEST);

        $commentService->createComment($request);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Comment created'], Response::HTTP_OK);
    }

    /**
     * @Route("/comment/{id}", name="edit_comment", methods={"PATCH"})
     */
    public function edit(Comment $comment, EntityManagerInterface $entityManager, CommentService $commentService, Request $request): JsonResponse
    {
        $newText = $request->get('text');
        if (!$newText) {
            return new JsonResponse(['message' => 'Missing text'], Response::HTTP_BAD_REQUEST);
        }

        if ($newText === $comment->getText()){
            return new JsonResponse(['message' => 'Text is no changed'], Response::HTTP_BAD_REQUEST);
        }


        if ($commentService->isCurrentUserAuthor($comment) || $this->isGranted('ROLE_ADMIN')) {
            $commentService->editComment($comment, $newText);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Comment edited'], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Forbidden access to edit comment'], Response::HTTP_FORBIDDEN);
    }

    /**
     * @Route("/comment/{id}", name="delete_comment", methods={"DELETE"})
     */
    public function delete(Comment $comment, EntityManagerInterface $entityManager, CommentService $commentService): JsonResponse
    {
        if ($commentService->isCurrentUserAuthor($comment) || $this->isGranted('ROLE_ADMIN')) {
            $commentService->deleteComment($comment);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Comment deleted'], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Forbidden access to delete comment'], Response::HTTP_FORBIDDEN);
    }
}
