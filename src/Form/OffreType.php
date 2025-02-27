<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Offre;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Champs liés à l'entité Offre
            ->add('dateDebutDisponibilite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début de disponibilité',
            ])
            ->add('dateFinDisponibilite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin de disponibilité',
            ])
            ->add('lieuGarage', TextType::class, [
                'label' => 'Lieu du garage',
            ])
            ->add('prix', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Prix de location',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
            ])
            // Champs pour la Voiture (non mappés)
            ->add('marque', TextType::class, [
                'mapped' => false,
                'label' => 'Marque',
            ])
            ->add('modele', TextType::class, [
                'mapped' => false,
                'label' => 'Modèle',
            ])
            ->add('immatriculation', TextType::class, [
                'mapped' => false,
                'label' => 'Immatriculation',
            ])
            ->add('annee', IntegerType::class, [
                'mapped' => false,
                'label' => 'Année',
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'mapped' => false,
                'label' => 'Nombre de places',
            ])
            ->add('volumeCoffre', IntegerType::class, [
                'mapped' => false,
                'label' => 'Volume du coffre',
            ])
            ->add('typeEssence', TextType::class, [
                'mapped' => false,
                'label' => 'Type d\'essence',
            ])
           
            ->add('livraisonTarifs', MoneyType::class, [
                'mapped' => false,
                'currency' => 'EUR',
                'label' => 'Tarifs de livraison',
            ])
            ->add('livraisonDisponibilite', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Livraison disponible',
            ])
            // Champ pour l'upload de photos (non mappé)
            ->add('photos', FileType::class, [
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'label' => 'Photos du véhicule',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}

