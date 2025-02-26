<?php
namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class ProfileController extends AbstractController
{
    #[Route('/profile/edit', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement de l'upload de la photo de profil
            /** @var UploadedFile $profileFile */
            $profileFile = $form->get('profileImage')->getData();

            if ($profileFile) {
                $newFilename = uniqid() . '.' . $profileFile->guessExtension();
                try {
                    $profileFile->move(
                        $this->getParameter('profile_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');
                }
                $user->setProfileImage($newFilename);
            }

            // Traitement du changement de mot de passe
            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData(); // Retourne le nouveau mot de passe s'il est saisi

            if ($oldPassword || $newPassword) {
                // Vérifie que l'utilisateur a saisi l'ancien mot de passe
                if (!$oldPassword) {
                    $this->addFlash('error', 'Veuillez saisir votre ancien mot de passe pour changer le mot de passe.');
                    return $this->redirectToRoute('app_profile');
                }
                // Vérifie que l'ancien mot de passe est correct
                if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                    $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
                    return $this->redirectToRoute('app_profile');
                }
                // Si un nouveau mot de passe a été saisi, le mettre à jour
                if ($newPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profile.html.twig', [
            'profileForm' => $form->createView(),
            'user' => $user,
        ]);

    }

    #[Route('/profile/remove-photo', name: 'profile_remove_photo', methods: ['POST'])]
    public function removePhoto(EntityManagerInterface $entityManager, UserInterface $user)
    {
        // Suppression de la photo et remise à l'image par défaut
        $user->setProfileImage(null);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre photo de profil a été supprimée.');
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/profile/delete-account', name: 'profile_delete_account', methods: ['POST'])]
    public function deleteAccount(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, UserInterface $user)
    {
        // Déconnexion de l'utilisateur avant suppression
        $tokenStorage->setToken(null);

        // Suppression de l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        return $this->redirectToRoute('homepage');
    }

    #[Route('/profile/upload-photo', name: 'profile_upload_photo', methods: ['POST'])]
    public function uploadPhoto(Request $request, EntityManagerInterface $entityManager, UserInterface $user)
    {
        $file = $request->files->get('profileImage');

        if ($file) {
            $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/profile_images';

            // Générer un nom unique pour la photo
            $newFilename = uniqid() . '.' . $file->guessExtension();

            // Déplacer le fichier uploadé
            $file->move($uploadsDirectory, $newFilename);

            // Supprimer l'ancienne photo si ce n'est pas la photo par défaut
            if ($user->getProfileImage() && $user->getProfileImage() !== 'default-photo.png') {
                @unlink($uploadsDirectory . '/' . $user->getProfileImage());
            }

            // Mettre à jour le profil de l'utilisateur
            $user->setProfileImage($newFilename);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre photo de profil a été mise à jour.');
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors du téléchargement.');
        }

        return $this->redirectToRoute('app_profile');
    }

}

