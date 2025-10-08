<?php

namespace App\DataFixtures;

use App\Entity\TypeDocument;
use App\Entity\NatureContrat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TypeDocumentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Types de documents obligatoires
        $typesObligatoires = [
            'Contrat de travail' => 'Document contractuel principal',
            'Fiche de paie' => 'Bulletin de salaire mensuel',
            'Attestation de travail' => 'Certificat d\'emploi',
            'Carte d\'identité' => 'Pièce d\'identité officielle',
            'CV' => 'Curriculum vitae du candidat',
            'Diplômes' => 'Certificats et diplômes',
            'Photo d\'identité' => 'Photo pour badge et documents officiels'
        ];

        // Types de documents optionnels
        $typesOptionnels = [
            'Certificat médical' => 'Certificat d\'aptitude médicale',
            'Permis de conduire' => 'Permis de conduire si nécessaire',
            'Attestation de formation' => 'Certificats de formation professionnelle',
            'Lettre de motivation' => 'Lettre de candidature',
            'Recommandations' => 'Lettres de recommandation'
        ];

        $natureContrats = $manager->getRepository(NatureContrat::class)->findAll();

        // Créer les types obligatoires
        foreach ($typesObligatoires as $nom => $description) {
            $typeDocument = new TypeDocument();
            $typeDocument->setNom($nom);
            $typeDocument->setDescription($description);
            $typeDocument->setObligatoire(true);

            // Associer à tous les types de contrats
            foreach ($natureContrats as $natureContrat) {
                $typeDocument->addNatureContrat($natureContrat);
            }

            $manager->persist($typeDocument);
        }

        // Créer les types optionnels
        foreach ($typesOptionnels as $nom => $description) {
            $typeDocument = new TypeDocument();
            $typeDocument->setNom($nom);
            $typeDocument->setDescription($description);
            $typeDocument->setObligatoire(false);

            // Associer seulement à certains types de contrats
            if (in_array($nom, ['Certificat médical', 'Permis de conduire'])) {
                foreach ($natureContrats as $natureContrat) {
                    $typeDocument->addNatureContrat($natureContrat);
                }
            } else {
                // Pour les autres, associer seulement aux CDI et CDD
                foreach ($natureContrats as $natureContrat) {
                    if (in_array($natureContrat->getLibelle(), ['CDI', 'CDD'])) {
                        $typeDocument->addNatureContrat($natureContrat);
                    }
                }
            }

            $manager->persist($typeDocument);
        }

        $manager->flush();
    }

}
