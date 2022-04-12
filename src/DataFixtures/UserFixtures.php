<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends BaseFixture
{
    /** @var UserPasswordHasherInterface */
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(User::class, '', 10, function (User $user, $count) {
            $password = $this->encoder->hashPassword($user, 'password');
            $user->setPassword($password);
            $user->setEmail("test_$count@test.sk");
            $user->setName($this->faker->name);
        });

        $manager->flush();
    }
}
