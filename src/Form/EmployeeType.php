<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\NatureContrat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le prénom'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@uiass.rh'
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+212 6XX XXX XXX'
                ]
            ])
            ->add('hireDate', DateType::class, [
                'label' => 'Date d\'embauche',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('position', TextType::class, [
                'label' => 'Poste',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Développeur, Manager, etc.'
                ]
            ])
            ->add('department', ChoiceType::class, [
                'label' => 'Département',
                'choices' => [
                    'Ressources Humaines' => 'RH',
                    'Informatique' => 'IT',
                    'Finance' => 'Finance',
                    'Marketing' => 'Marketing',
                    'Ventes' => 'Ventes',
                    'Production' => 'Production',
                    'Administration' => 'Administration'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => $options['is_new'] ?? true,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '••••••••'
                ]
            ])
            // Section Contrat
            ->add('natureContrat', EntityType::class, [
                'class' => NatureContrat::class,
                'choice_label' => 'libelle',
                'label' => 'Type de contrat',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateDebutContrat', DateType::class, [
                'label' => 'Date de début du contrat',
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateFinContrat', DateType::class, [
                'label' => 'Date de fin du contrat',
                'widget' => 'single_text',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
            'is_new' => true,
        ]);
    }
}
