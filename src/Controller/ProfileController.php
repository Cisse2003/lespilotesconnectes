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
                $newFilename = uniqid().'.'.$profileFile->guessExtension();
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
}

