<?php

// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        //$user->setName("Javier");
        //$user->setSurname("Núñez");
        $user->setEmail('admin@mygoals.es');
        $roles = ["ROLE_ADMIN"];
        $user->setRoles($roles);
        $password = $this->hasher->hashPassword($user, '12345');
        $user->setPassword($password);

        $manager->persist($user);

        $manager->flush();

        $user = new User();
        //$user->setName("Javier");
        //$user->setSurname("Núñez");
        $user->setEmail('user@mygoals.es');
        $roles = ["ROLE_USER"];
        $user->setRoles($roles);
        $password = $this->hasher->hashPassword($user, '12345');
        $user->setPassword($password);

        $manager->persist($user);

        $manager->flush();





    }
}
