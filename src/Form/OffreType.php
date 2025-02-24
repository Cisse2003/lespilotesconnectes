<?php


namespace App\Form;

use App\Entity\Offre;
use App\Entity\Voiture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use App\Form\LivraisonType; 

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', FileType::class, [
                'label'    => 'Photos de la voiture',
                'mapped'   => false,
                'multiple' => true,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes'        => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG/PNG)',
                    ])
                ],
            ])
            ->add('voiture', EntityType::class, [
                'class'        => Voiture::class,
                'choice_label' => function (Voiture $voiture) {
                    return $voiture->getMarque() . ' ' . $voiture->getModele() . ' (' . $voiture->getAnnee() . ')';
                },
                'label'        => 'Sélectionnez une voiture',
                'placeholder'  => 'Choisissez une voiture',
            ])
            ->add('lieuGarage', TextType::class, [
                'label' => 'Lieu de garage',
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix de location (€/jour)',
            ])
            ->add('disponibilite', ChoiceType::class, [
                'label'   => 'Disponibilité',
                'choices' => [
                    'Disponible'   => true,
                    'Indisponible' => false,
                ],
                'expanded' => true,
            ])
            ->add('dateDisponibilite', DateType::class, [
                'label'   => 'Disponible à partir de',
                'widget'  => 'single_text',
                'required'=> false,
            ])
            ->add('proposeLivraison', CheckboxType::class, [
                'label'    => 'Proposer une livraison ?',
                'required' => false,
                'mapped'   => false,
            ])
            ->add('livraison', LivraisonType::class, [
                'label'    => 'Informations de livraison',
                'required' => false,
                'mapped'   => false, // Ce champ n'est pas mappé à Offre
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Déposer l\'offre',
                'attr'  => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}

