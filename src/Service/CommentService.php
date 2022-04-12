<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class CommentService
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function getMissingDataForCreateComment($request): array
    {
        $missingData = [];

        $authorName = $request->get('username');
        $text = $request->get('text');
        $email = $request->get('email');
        $parent = $request->get('parent');
        $article = $request->get('article');

        if (!$text)
            $missingData[] = ['type' => 'text', 'message' => 'Text is missing'];

        if (!$article) {
            $missingData[] = ['type' => 'article', 'message' => 'Article is missing'];
        } elseif (!$this->entityManager->getRepository(Article::class)->find($article)) {
            $missingData[] = ['type' => 'article', 'message' => 'Article not exist'];
        }

        if ($parent && !$this->entityManager->getRepository(Comment::class)->find($parent))
            $missingData[] = ['type' => 'parent', 'message' => 'Parent comment not exist'];

        if (!$this->security->getUser()) {
            if (!$authorName)
                $missingData[] = ['type' => 'authorName', 'message' => 'Username is missing'];
            if (!$email)
                $missingData[] = ['type' => 'email', 'message' => 'Email is missing'];
        }

        return $missingData;
    }

    public function createComment(Request $request): Comment
    {
        $authorName = $request->get('username');
        $text = $request->get('text');
        $email = $request->get('email');
        $parent = $request->get('parent');
        $article = $request->get('article');

        $comment = new Comment();
        $comment->setIsDeleted(false);
        $comment->setText($text);
        $comment->setCreatedAt(new \DateTime());
        if ($this->security->getUser()) {
            $comment->setAuthor($this->security->getUser());
        } else {
            $comment->setAuthorEmail($email);
            $comment->setAuthorName($authorName);
        }
        if ($parent) {
            $parentEntity = $this->entityManager->getRepository(Comment::class)->find($parent);
            $comment->setParent($parentEntity);
        }
        $articleEntity = $this->entityManager->getRepository(Article::class)->find($article);
        $comment->setArticle($articleEntity);
        $this->entityManager->persist($comment);

        return $comment;
    }

    public function editComment(Comment $comment, string $newText)
    {
        $comment->setEditedAt(new \DateTime());
        $comment->setEditedBy($this->security->getUser());
        $comment->setText($newText);
    }

    public function deleteComment(Comment $comment)
    {
        $comment->setIsDeleted(true);
        $comment->setAuthor(null);
        $comment->setAuthorName(null);
        $comment->setAuthorEmail(null);
        $comment->setEditedAt(null);
        $comment->setEditedBy(null);
    }

    public function isCurrentUserAuthor(Comment $comment): bool
    {
        if ($comment->getAuthor() && $comment->getAuthor() === $this->security->getUser())
            return true;

        return false;
    }

    public function wipeCommentsInArticle(Article $articleEntity)
    {
        $comments = $articleEntity->getComments();
        foreach ($comments as $comment) {
            $this->entityManager->remove($comment);
        }
    }
}