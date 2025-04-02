<?php

namespace App\DataFixtures;

use App\Entity\Administrateur;
use App\Entity\Juriste;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Administrateur();
        $admin->setNom('Cisse');
        $admin->setPrenom('Pape');
        $admin->setEmail('cissepape678@gmail.com');
        $admin->setRole('ROLE_ADMIN');

         $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));

        $manager->persist($admin);

        // Création d'un juriste
        $juriste = new Juriste();
        $juriste->setNom('Durand');
        $juriste->setPrenom('Marc');
        $juriste->setEmail('marc.durand@juriste.com');
        $juriste->setRole('ROLE_JURISTE');
        $juriste->setPassword($this->passwordHasher->hashPassword($juriste, 'juriste123'));

        $manager->persist($juriste);

        $manager->flush();
    }
}
