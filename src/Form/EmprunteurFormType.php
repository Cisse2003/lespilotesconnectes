<?php

namespace App\Form;

use App\Entity\Emprunteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class EmprunteurFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire.'])
                ]
            ])
            ->add('numeroPermis', TextType::class, [
                'label' => 'Numéro de permis',
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de permis est obligatoire.']),
                    new Regex([
                        'pattern' => "/^[A-Z0-9]{12}$/",
                        'message' => "Le numéro de permis doit respecter le format français (ex: 123456789012)."
                    ])
                ]
            ])
            ->add('dateExpiration', DateType::class, [
                'label' => 'Date d\'expiration',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date d\'expiration est obligatoire.']),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date d\'expiration ne peut pas être passée.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emprunteur::class,
        ]);
    }
}
