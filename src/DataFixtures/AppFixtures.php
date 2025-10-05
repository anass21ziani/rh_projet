<?php

namespace App\DataFixtures;

use App\Entity\Employe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $administrateurRh = new Employe();
        $administrateurRh->setEmail('admin@uiass.rh');
        $administrateurRh->setRoles(['ROLE_ADMINISTRATEUR_RH']);
        $administrateurRh->setPassword($this->passwordHasher->hashPassword($administrateurRh, 'password123'));
        $manager->persist($administrateurRh);

        $responsableRh = new Employe();
        $responsableRh->setEmail('rh@uiass.rh');
        $responsableRh->setRoles(['ROLE_RESPONSABLE_RH']);
        $responsableRh->setPassword($this->passwordHasher->hashPassword($responsableRh, 'password123'));
        $manager->persist($responsableRh);

        $employe = new Employe();
        $employe->setEmail('employe@uiass.rh');
        $employe->setRoles(['ROLE_EMPLOYE']);
        $employe->setPassword($this->passwordHasher->hashPassword($employe, 'password123'));
        $manager->persist($employe);

        $manager->flush();
    }
}
