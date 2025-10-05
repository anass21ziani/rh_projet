<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use App\Entity\NatureContrat;
use App\Entity\EmployeeContrat;
use App\Entity\Dossier;
use App\Entity\Placard;
use App\Entity\Document;
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
        // Créer les types de contrats
        $cdi = new NatureContrat();
        $cdi->setLibelle('CDI');
        $cdi->setDescription('Contrat à Durée Indéterminée');
        $manager->persist($cdi);

        $cdd = new NatureContrat();
        $cdd->setLibelle('CDD');
        $cdd->setDescription('Contrat à Durée Déterminée');
        $manager->persist($cdd);

        $stage = new NatureContrat();
        $stage->setLibelle('Stage');
        $stage->setDescription('Période de stage');
        $manager->persist($stage);

        // Créer les utilisateurs
        $administrateurRh = new Employee();
        $administrateurRh->setFirstName('Admin');
        $administrateurRh->setLastName('RH');
        $administrateurRh->setEmail('admin@uiass.rh');
        $administrateurRh->setPhone('+212 6XX XXX XXX');
        $administrateurRh->setHireDate(new \DateTime('2020-01-01'));
        $administrateurRh->setPosition('Administrateur RH');
        $administrateurRh->setDepartment('RH');
        $administrateurRh->setRoles(['ROLE_ADMINISTRATEUR_RH']);
        $administrateurRh->setPassword($this->passwordHasher->hashPassword($administrateurRh, 'password123'));
        $manager->persist($administrateurRh);

        $responsableRh = new Employee();
        $responsableRh->setFirstName('Responsable');
        $responsableRh->setLastName('RH');
        $responsableRh->setEmail('rh@uiass.rh');
        $responsableRh->setPhone('+212 6XX XXX XXX');
        $responsableRh->setHireDate(new \DateTime('2021-01-01'));
        $responsableRh->setPosition('Responsable RH');
        $responsableRh->setDepartment('RH');
        $responsableRh->setRoles(['ROLE_RESPONSABLE_RH']);
        $responsableRh->setPassword($this->passwordHasher->hashPassword($responsableRh, 'password123'));
        $manager->persist($responsableRh);

        $employe = new Employee();
        $employe->setFirstName('John');
        $employe->setLastName('Doe');
        $employe->setEmail('employe@uiass.rh');
        $employe->setPhone('+212 6XX XXX XXX');
        $employe->setHireDate(new \DateTime('2022-01-01'));
        $employe->setPosition('Développeur');
        $employe->setDepartment('IT');
        $employe->setRoles(['ROLE_EMPLOYEE']);
        $employe->setPassword($this->passwordHasher->hashPassword($employe, 'password123'));
        $manager->persist($employe);

        // Créer un contrat pour l'employé
        $contrat = new EmployeeContrat();
        $contrat->setEmployee($employe);
        $contrat->setNatureContrat($cdi);
        $contrat->setStartDate(new \DateTime('2022-01-01'));
        $contrat->setStatut('actif');
        $manager->persist($contrat);

        // Créer un dossier pour l'employé
        $dossier = new Dossier();
        $dossier->setEmployee($employe);
        $dossier->setTitle('Dossier administratif');
        $dossier->setDescription('Dossier contenant tous les documents administratifs de l\'employé');
        $dossier->setType('administratif');
        $manager->persist($dossier);

        // Créer un placard pour le dossier
        $placard = new Placard();
        $placard->setDossier($dossier);
        $placard->setName('Placard A1');
        $placard->setLocation('Bureau RH - Étage 2');
        $manager->persist($placard);

        // Créer un document exemple
        $document = new Document();
        $document->setDossier($dossier);
        $document->setReference('DOC-2025-001');
        $document->setFilename('contrat_emploi.pdf');
        $document->setFileType('application/pdf');
        $document->setFilePath('/documents/contrat_emploi.pdf');
        $document->setUploadedBy('admin@uiass.rh');
        $manager->persist($document);

        $manager->flush();
    }
}
