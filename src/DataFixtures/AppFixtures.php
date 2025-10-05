<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use App\Entity\NatureContrat;
use App\Entity\EmployeeContrat;
use App\Entity\Dossier;
use App\Entity\Placard;
use App\Entity\Document;
use App\Entity\Demande;
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

        // Créer des demandes de test
        $demande1 = new Demande();
        $demande1->setTitre('Demande de congé');
        $demande1->setContenu('Bonjour, je souhaiterais prendre une semaine de congé du 15 au 22 janvier 2025 pour des raisons personnelles. Pourriez-vous me confirmer si cela est possible ?');
        $demande1->setEmploye($employe);
        $demande1->setStatut('en_attente');
        $manager->persist($demande1);

        $demande2 = new Demande();
        $demande2->setTitre('Demande de formation');
        $demande2->setContenu('Je souhaiterais suivre une formation sur Symfony Framework pour améliorer mes compétences techniques. Cette formation durerait 3 jours et coûterait environ 800€. Qu\'en pensez-vous ?');
        $demande2->setEmploye($employe);
        $demande2->setStatut('acceptee');
        $demande2->setResponsableRh($responsableRh);
        $demande2->setReponse('Excellente idée ! Cette formation est tout à fait justifiée pour votre poste. Je valide votre demande. Vous pouvez procéder à l\'inscription.');
        $demande2->setDateReponse(new \DateTimeImmutable('2024-12-20 14:30:00'));
        $manager->persist($demande2);

        $demande3 = new Demande();
        $demande3->setTitre('Réclamation sur les horaires');
        $demande3->setContenu('Je rencontre des difficultés avec mes horaires de travail. Actuellement, je travaille de 8h à 17h, mais j\'aimerais pouvoir commencer à 9h et finir à 18h pour des raisons de transport. Est-ce possible ?');
        $demande3->setEmploye($employe);
        $demande3->setStatut('refusee');
        $demande3->setResponsableRh($responsableRh);
        $demande3->setReponse('Je comprends votre situation, mais malheureusement, les horaires de 8h-17h sont nécessaires pour la coordination avec l\'équipe. Nous pourrions peut-être envisager un aménagement ponctuel si nécessaire.');
        $demande3->setDateReponse(new \DateTimeImmutable('2024-12-18 10:15:00'));
        $manager->persist($demande3);

        $manager->flush();
    }
}
