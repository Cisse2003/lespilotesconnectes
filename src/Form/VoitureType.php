<?php
// src/Form/VoitureType.php
namespace App\Form;

use App\Entity\Voiture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoitureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('marque', TextType::class, [
                'attr' => [
                    'placeholder' => 'Sélectionnez ou saisissez une marque',
                    'list' => 'liste-marques'
                ]
            ])
            ->add('modele', TextType::class, [
                'attr' => ['placeholder' => 'Ex: Clio, Golf, Corolla...']
            ])
            ->add('immatriculation', TextType::class, [
                'attr' => ['placeholder' => 'Ex: XX-123-XX']
            ])
            ->add('annee', IntegerType::class, [
                'attr' => ['placeholder' => '2023', 'min' => 1970, 'max' => 2050]
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'attr' => ['placeholder' => 'Ex: 5', 'min' => 1, 'max' => 9]
            ])
            ->add('volumeCoffre', IntegerType::class, [
                'attr' => ['placeholder' => 'Ex: 300 (L)']
            ])
            ->add('typeEssence', ChoiceType::class, [
                'choices' => [
                    'Essence'     => 'Essence',
                    'Diesel'      => 'Diesel',
                    'Electrique'  => 'Electrique',
                    'Hybride'     => 'Hybride'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Voiture::class,
        ]);
    }
}

