<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Vérifie si l'email existe déjà
                $existingUser = $entityManager->getRepository(Utilisateur::class)
                    ->findOneBy(['email' => $user->getEmail()]);

                if ($existingUser) {
                    $this->addFlash('error', 'Cet email est déjà utilisé.');
                    return $this->redirectToRoute('app_register');
                }

                // Génère un jeton de confirmation unique
                $user->setConfirmationToken(Uuid::v4());

                // Encode le mot de passe
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                // Désactive le compte jusqu'à la confirmation par e-mail
                $user->setIsVerified(false);

                // Persiste l'utilisateur sans activation
                $entityManager->persist($user);
                $entityManager->flush();

                // Envoi du mail de confirmation
                $email = (new Email())
                    ->from('lespilotes@lespilotesconnectes.com')
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse e-mail - PRÉKAR')
                    ->html(
                        '<p>Bonjour ' . $user->getNom() . ',</p>' .
                        '<p>Merci de vous être inscrit sur <strong>PRÉKAR</strong> ! Pour activer votre compte, veuillez confirmer votre adresse e-mail en cliquant sur le lien ci-dessous :</p>' .
                        '<p><a href="https://www.lespilotesconnectes.com/confirm/' . $user->getConfirmationToken() . '" style="display:inline-block;padding:10px 20px;color:#fff;background:#28a745;text-decoration:none;border-radius:5px;">Confirmer mon compte</a></p>' .
                        '<p>Si vous n\'avez pas demandé cette inscription, ignorez simplement ce message.</p>' .
                        '<p>À bientôt sur PRÉKAR !</p>' .
                        '<p>L\'équipe PRÉKAR</p>'
                    );

                $mailer->send($email);

                // Ajoute un message flash
                $this->addFlash('success', 'Un e-mail de confirmation vous a été envoyé. Veuillez vérifier votre boîte de réception.');

                return $this->redirectToRoute('app_login');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/confirm/{token}', name: 'app_confirm_email')]
    public function confirmEmail(
        string $token,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        // Recherche de l'utilisateur par son token
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Jeton invalide ou utilisateur non trouvé.');
            return $this->redirectToRoute('app_login');
        }

        // Activer le compte
        $user->setIsVerified(true);
        $user->setConfirmationToken(null); // Supprime le token après confirmation
        $entityManager->flush();

        // Envoi du mail de bienvenue
        $email = (new Email())
            ->from('lespilotes@lespilotesconnectes.com')
            ->to($user->getEmail())
            ->subject('Bienvenue chez PRÉKAR - Louez et empruntez des voitures facilement !')
            ->html(
                '<p>Bonjour ' . $user->getNom() . ',</p>' .
                '<p>Bienvenue sur <strong>PRÉKAR</strong>, votre plateforme de location et d\'emprunt de voitures ! 🚗✨</p>' .
                '<p>Avec PRÉKAR, vous pouvez facilement louer un véhicule ou proposer le vôtre à la communauté.</p>' .
                '<p>Commencez dès maintenant en visitant notre site :</p>' .
                '<p><a href="https://www.lespilotesconnectes.com/login" style="display:inline-block;padding:10px 20px;color:#fff;background:#007BFF;text-decoration:none;border-radius:5px;">Accéder à PRÉKAR</a></p>' .
                '<p>Si vous avez des questions, notre équipe est là pour vous aider.</p>' .
                '<p>À très bientôt sur PRÉKAR ! 🚀</p>' .
                '<p>L\'équipe PRÉKAR</p>'
            );

        $mailer->send($email);

        $this->addFlash('success', 'Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}
