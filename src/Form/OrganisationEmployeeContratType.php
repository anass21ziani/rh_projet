<?php

namespace App\Form;

use App\Entity\OrganisationEmployeeContrat;
use App\Entity\Organisation;
use App\Entity\EmployeeContrat;
use App\Repository\OrganisationRepository;
use App\Repository\EmployeeContratRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationEmployeeContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organisation', ChoiceType::class, [
                'label' => 'Organisation',
                'choices' => $this->getOrganisationChoices($options['organisationRepository']),
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('employeeContrat', ChoiceType::class, [
                'label' => 'Contrat Employé',
                'choices' => $this->getEmployeeContratChoices($options['employeeContratRepository']),
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de Début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de Fin',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrganisationEmployeeContrat::class,
            'organisationRepository' => null,
            'employeeContratRepository' => null,
        ]);
    }

    private function getOrganisationChoices($organisationRepository): array
    {
        $choices = [];
        if ($organisationRepository) {
            $organisations = $organisationRepository->findAll();
            foreach ($organisations as $organisation) {
                $choices[$organisation->getDossierDesignation()] = $organisation->getId();
            }
        }
        return $choices;
    }

    private function getEmployeeContratChoices($employeeContratRepository): array
    {
        $choices = [];
        if ($employeeContratRepository) {
            $contrats = $employeeContratRepository->findAll();
            foreach ($contrats as $contrat) {
                $employe = $contrat->getEmploye();
                $choices[$employe->getPrenom() . ' ' . $employe->getNom() . ' (' . $contrat->getNatureContrat() . ')'] = $contrat->getId();
            }
        }
        return $choices;
    }
}
