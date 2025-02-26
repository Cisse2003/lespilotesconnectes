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
            // Vérifie si l'email existe déjà
            $existingUser = $entityManager->getRepository(Utilisateur::class)
                ->findOneBy(['email' => $user->getEmail()]);

            try {
                // Encode le mot de passe
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                // Persiste et sauvegarde l'utilisateur
                $entityManager->persist($user);
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


                $mailer->send($email); // Envoi du mail

                // Ajoute un message flash de succès
                $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez vous connecter.');

                // Redirige vers la page de connexion
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
}

