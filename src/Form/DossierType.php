<?php

namespace App\Form;

use App\Entity\Dossier;
use App\Entity\Employee;
use App\Entity\Placard;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DossierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('employee', EntityType::class, [
                'label' => 'Employé',
                'class' => Employee::class,
                'choice_label' => 'fullName',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre du dossier',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Dossier administratif, Dossier médical'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Description du dossier'
                ]
            ])
            ->add('placard', EntityType::class, [
                'label' => 'Placard',
                'class' => Placard::class,
                'choice_label' => function(Placard $placard) {
                    return $placard->getName() . ' (' . $placard->getLocation() . ')';
                },
                'required' => false,
                'placeholder' => 'Sélectionner un placard (optionnel)',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut du dossier',
                'choices' => [
                    'En attente' => 'pending',
                    'En cours' => 'in_progress',
                    'Complété' => 'completed'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dossier::class,
        ]);
    }
}
