<?php

namespace App\Form;

use App\Entity\Offre;
use App\Form\VoitureType;
use App\Form\LivraisonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ✅ Formulaire pour la voiture
            ->add('voiture', VoitureType::class, [
                'label' => false,
            ])

            // ✅ Date de disponibilité
            ->add('dateDebutDisponibilite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début'
            ])
            ->add('dateFinDisponibilite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin'
            ])

            // ✅ Lieu de garage et prix
            ->add('lieuGarage', TextType::class, [
                'attr' => ['placeholder' => 'Ex: Paris 12ème']
            ])
            ->add('prix', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Prix (€/jour)',
                'attr' => ['min' => '0', 'placeholder' => 'Ex: 50']
            ])

            // ✅ Description de l'offre
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Décrivez brièvement le véhicule...',
                    'rows' => 3
                ],
                'required' => false
            ])

            // ✅ Photos du véhicule
            ->add('photos', FileType::class, [
                'label' => 'Photos du véhicule',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*'
                ]
            ])

            // ✅ Formulaire de la livraison (inclusion du `LivraisonType`)
            ->add('livraison', LivraisonType::class, [
                'label' => false,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}
