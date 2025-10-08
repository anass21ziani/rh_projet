<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\Dossier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('abreviation', TextType::class, [
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
            ])
            ->add('obligatoire', CheckboxType::class, [
                'label' => 'Document Obligatoire',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'disallowEmptyMessage' => 'Veuillez sélectionner un fichier',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png,.gif'
                ]
            ])
            ->add('filename', TextType::class, [
                'label' => 'Nom du Fichier',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('fileType', TextType::class, [
                'label' => 'Type de Fichier',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('filePath', TextType::class, [
                'label' => 'Chemin du Fichier',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Référence unique du document'
                ]
            ])
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Date de Création',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('uploadedBy', TextType::class, [
                'label' => 'Uploadé par',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dossier', EntityType::class, [
                'class' => Dossier::class,
                'choice_label' => 'nom',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
            'is_new' => true,
        ]);
    }
}