<?php

// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Category;
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

        $category1 = new Category();
        $category1->setName("Trabajo");
        $category1->setColor("#FE9A37");
        $category1->setIcon("bi bi-person-workspace");

        $manager->persist($category1);

        $manager->flush();

        $category2 = new Category();
        $category2->setName("Deporte");
        $category2->setColor("#2A9689");
        $category2->setIcon("bi bi-dribbble");

        $manager->persist($category2);

        $manager->flush();

        $category3 = new Category();
        $category3->setName("Hogar");
        $category3->setColor("#AD46FF");
        $category3->setIcon("bi bi-house");

        $manager->persist($category3);

        $manager->flush();

        $category4 = new Category();
        $category4->setName("Familia");
        $category4->setColor("#AD46FF");
        $category4->setIcon("bi bi-people");

        $manager->persist($category4);

        $manager->flush();








    }
}
