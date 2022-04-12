<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createCommentFixtures('_1_', 1700, 0);
        $manager->flush();

        $this->createCommentFixtures('_2_', 550);
        $manager->flush();

        $this->createCommentFixtures('_3_', 800, 80);
        $manager->flush();

        $this->createCommentFixtures('_4_', 900, 100);
        $manager->flush();

        $this->createCommentFixtures('_5_', 900, 100);
        $manager->flush();
    }

    private function createCommentFixtures(string $countPrefix, int $count, int $trueChanceAddParent = 50)
    {
        $this->createMany(Comment::class, $countPrefix, $count, function (Comment $comment) use ($trueChanceAddParent) {
            $chanceForRegisteredUser = 70;
            $chanceForDeletedComment = 10;
            $chanceForEditedComment = 10;

            if ($this->faker->boolean($chanceForRegisteredUser)) {
                $comment->setAuthor($this->getRandomReference(User::class));
            } else {
                $comment->setAuthorEmail($this->faker->email);
                $comment->setAuthorName($this->faker->name);
            }

            $comment->setIsDeleted($this->faker->boolean($chanceForDeletedComment));

            $comment->setText($this->faker->paragraph(8));
            $comment->setCreatedAt($this->faker->dateTime);

            if ($this->faker->boolean($chanceForEditedComment)) {
                $comment->setEditedAt($this->faker->dateTime);
                $comment->setEditedBy($this->getRandomReference(User::class));
            }

            if ($this->faker->boolean($trueChanceAddParent)) {
                $parentComment = $this->getRandomReference(Comment::class);
                $comment->setParent($parentComment);
                $comment->setArticle($parentComment->getArticle());
            } else {
                $comment->setArticle($this->getRandomReference(Article::class));
            }
        });
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ArticleFixtures::class,
        ];
    }
}