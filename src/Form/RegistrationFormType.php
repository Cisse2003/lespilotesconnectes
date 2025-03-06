<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email *',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une adresse email',
                    ]),
                    new Email([
                        'message' => 'Veuillez entrer une adresse email valide.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe *'],
                'second_options' => ['label' => 'Confirmez le mot de passe *'],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
                        'message' => 'Votre mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial.',
                    ]),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom *',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom ne doit pas dépasser 50 caractères.',
                    ]),
                    new Regex([
                        'pattern' => "/^[a-zA-ZÀ-ÿ\-']+$/",
                        'message' => 'Le nom ne doit contenir que des lettres, tirets et apostrophes.',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom *',
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le prénom ne doit pas dépasser 50 caractères.',
                    ]),
                    new Regex([
                        'pattern' => "/^[a-zA-ZÀ-ÿ\-']+$/",
                        'message' => 'Le prénom ne doit contenir que des lettres, tirets et apostrophes.',
                    ]),
                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => "/^(?:\+33|0)[1-9](?:[\s.-]?[0-9]{2}){4}$/",
                        'message' => 'Le numéro de téléphone doit être un numéro français valide.',
                    ]),
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'L\'adresse ne doit pas dépasser 255 caractères.',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les conditions d\'utilisation *',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions d\'utilisation.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
