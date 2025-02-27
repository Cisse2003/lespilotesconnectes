<?php
// src/Form/OffreType.php
namespace App\Form;

use App\Entity\Offre;
use App\Form\VoitureType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Intégration du sous-formulaire pour la voiture
            ->add('voiture', VoitureType::class, [
                'label' => false,
            ])
            ->add('dateDebutDisponibilite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début'
            ])
            ->add('dateFinDisponibilite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin'
            ])
            ->add('lieuGarage', TextType::class, [
                'attr' => ['placeholder' => 'Ex: Paris 12ème']
            ])
            ->add('prix', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Prix (€/jour)',
                'attr' => ['min' => '0', 'placeholder' => 'Ex: 50']
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Décrivez brièvement le véhicule...',
                    'rows' => 3
                ],
                'required' => false
            ])
            // Champs non mappés pour la livraison
            ->add('livraisonTarifs', IntegerType::class, [
                'mapped'   => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: 20', 'min' => '0']
            ])
            ->add('livraisonDisponibilite', CheckboxType::class, [
                'mapped'   => false,
                'required' => false,
                'label'    => 'Livraison disponible ?'
            ])
            // Champ non mappé pour les photos
            ->add('photos', FileType::class, [
                'mapped'   => false,
                'required' => false,
                'multiple' => true,
                'attr'     => ['accept' => 'image/*']
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

