<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $email = $request->request->get('email');

            $utilisateur = $entityManager->getRepository(Utilisateur::class)->findOneBy([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email
            ]);

            if (!$utilisateur) {
                $this->addFlash('error', 'Les informations fournies ne correspondent à aucun utilisateur.');
            } else {
                return $this->redirectToRoute('app_reset_password', ['id' => $utilisateur->getId()]);
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{id}', name: 'app_reset_password')]
    public function resetPassword(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);

        if (!$utilisateur) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.');
            } else {
                $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $newPassword));
                $entityManager->flush();

                $email = (new Email())
                    ->from('lespilotes@lespilotesconnectes.com')
                    ->to($utilisateur->getEmail())
                    ->subject('Votre mot de passe a été modifié')
                    ->html("
                    <p>Bonjour {$utilisateur->getPrenom()},</p>
                    <p>Votre mot de passe a été modifié avec succès.</p>
                    <p>Si vous n'êtes pas à l'origine de cette modification, veuillez nous contacter immédiatement.</p>
                    <p>Cordialement, <br>L\'équipe PRÉKAR !</p>
                ");

                $mailer->send($email);

                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Un email de confirmation vous a été envoyé.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'id' => $id,
            'utilisateur' => $utilisateur
        ]);
    }
}
