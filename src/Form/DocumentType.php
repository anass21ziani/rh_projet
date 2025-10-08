<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('abbreviation', TextType::class, [
                'label' => 'Abréviation',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: CIN, CV, DIP...'
                ]
            ])
            ->add('libelleComplet', TextType::class, [
                'label' => 'Libellé Complet',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Carte d\'identité nationale'
                ]
            ])
            ->add('typeDocument', TextType::class, [
                'label' => 'Type de Document',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Identité, Diplôme, RH...'
                ]
            ])
            ->add('usage', TextareaType::class, [
                'label' => 'Usage',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Description de l\'usage du document'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}