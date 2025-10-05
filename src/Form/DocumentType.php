<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\Dossier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocumentType extends AbstractType
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
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: DOC-2025-001'
                ]
            ])
            ->add('filename', TextType::class, [
                'label' => 'Nom du fichier',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: contrat_emploi.pdf'
                ]
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier',
                'mapped' => false,
                'required' => $options['is_new'] ?? true,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'image/jpeg',
                            'image/png',
                            'text/plain'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier valide (PDF, DOC, DOCX, JPG, PNG, TXT)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
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
            'data_class' => Document::class,
            'is_new' => true,
        ]);
    }
}
