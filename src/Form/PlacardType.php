<?php

namespace App\Form;

use App\Entity\Placard;
use App\Entity\Dossier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlacardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dossier', EntityType::class, [
                'label' => 'Dossier',
                'class' => Dossier::class,
                'choice_label' => 'title',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom du placard',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Placard A1, Armoire B2'
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Emplacement',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Bureau RH, Archives, Serveur'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Placard::class,
        ]);
    }
}
