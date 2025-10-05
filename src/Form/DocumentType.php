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
use App\Validator\FileExtension;

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
                    new FileExtension([
                        'extensions' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt', 'gif', 'bmp', 'tiff', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'mp4', 'avi', 'mov', 'wmv', 'mp3', 'wav', 'flac'],
                        'maxSize' => 10 * 1024 * 1024, // 10MB en bytes
                        'message' => 'Extensions autorisées : PDF, DOC, DOCX, JPG, PNG, TXT, GIF, BMP, TIFF, XLS, XLSX, PPT, PPTX, ZIP, RAR, MP4, AVI, MOV, WMV, MP3, WAV, FLAC'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.gif,.bmp,.tiff,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.mp4,.avi,.mov,.wmv,.mp3,.wav,.flac'
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
