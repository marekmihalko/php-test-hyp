<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Article::class, '', 150, function (Article $article, $count) {
            $article->setAuthor($this->getRandomReference(User::class));
            $article->setTitle($this->faker->text(100));
            $article->setText($this->faker->paragraph(150));
            $article->setCreatedAt($this->faker->dateTime);
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}