<?php

namespace App\Controller;

use App\Entity\Emprunteur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Offre;
use App\Repository\OffreRepository;
use App\Entity\Location;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Proprietaire;
use App\Entity\Utilisateur;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Litige;
use App\Repository\LocationRepository;
use App\Repository\AdministrateurRepository;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(LocationRepository $locationRepository,
                              AdministrateurRepository $adminRepository,
                              EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$admin) {
            throw $this->createAccessDeniedException("Accès refusé. Vous devez être administrateur.");
        }

        // Vérifier si l'administrateur existe
        if ($admin) {
            // Récupérer toutes les locations et recalculer la commission totale
            $locations = $locationRepository->findAll();
            $admin->calculerCommissionTotale($locations);

            // Sauvegarder la nouvelle commission
            $entityManager->persist($admin);
            $entityManager->flush();
        }
        return $this->render('admin/admin_dashboard.html.twig', [
            'admin' => $admin
        ]);
    }

    #[Route('/admin/commission/calculer', name: 'admin_calculer_commission')]
    public function calculerCommission(
        LocationRepository $locationRepository,
        AdministrateurRepository $adminRepository,
        EntityManagerInterface $entityManager
    ) {
        // Récupérer toutes les locations actives
        $locations = $locationRepository->findAll();

        // Récupérer l'administrateur (supposons qu'il y en ait un seul)
        $admin = $adminRepository->find(1); // À adapter selon ta logique

        if ($admin) {
            // Calculer et enregistrer la commission totale
            $admin->calculerCommissionTotale($locations);
            $entityManager->persist($admin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_dashboard'); // Adapter la redirection
    }

    #[Route('/utilisateur', name: 'utilisateurs')]
    public function manageUsers(EntityManagerInterface $entityManager): Response
    {
        $proprietaires = $entityManager->getRepository(Proprietaire::class)->findAll();
        $emprunteurs = $entityManager->getRepository(Emprunteur::class)->findAll();

        return $this->render('admin/utilisateurs.html.twig', [
            'proprietaires' => $proprietaires,
            'emprunteurs' => $emprunteurs,
        ]);
    }

    #[Route('/offres', name: 'offres')]
    public function manageOffers(OffreRepository $offreRepository): Response
    {
        $offres = $offreRepository->findBy(['disponibilite' => true]);

        return $this->render('admin/offres.html.twig', [
            'offres' => $offres,
        ]);
    }

    #[Route('/utilisateur/{id}/offres', name: 'offres_utilisateur')]
    public function userOffers(int $id, UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        dump($utilisateur); // Vérifiez si l'utilisateur est trouvé

        $proprietaire = $utilisateur->getProprietaire();

        dump($proprietaire); // Vérifiez si le propriétaire est bien récupéré

        if (!$proprietaire) {
            throw $this->createNotFoundException("Cet utilisateur n'est pas un propriétaire et n'a pas d'offres.");
        }

        $offres = $proprietaire->getOffres();

        return $this->render('admin/offres_utilisateur.html.twig', [
            'utilisateur' => $utilisateur,
            'offres' => $offres,
        ]);
    }


    #[Route('/utilisateur/{id}/suspendre', name: 'suspendre_utilisateur', methods: ['POST'])]
    public function suspendUser(int $id, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        $utilisateur->setSuspendedUntil(new \DateTime('+30 days'));
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur a été suspendu.");
        return $this->redirectToRoute('admin_utilisateurs');
    }

    #[Route('/utilisateur/{id}/supprimer', name: 'supprimer_utilisateur', methods: ['POST'])]
    public function deleteUser(
        int $id,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        Request $request
    ): Response {
        $utilisateur = $utilisateurRepository->find($id);
        if (!$utilisateur) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        // Récupérer la cause du bannissement depuis la requête (par exemple, un champ caché dans le formulaire)
        $causeBannissement = $request->request->get('cause_bannissement', 'Non respect des conditions d\'utilisation');

        // Préparer et envoyer l'email avant la suppression
        $email = (new Email())
            ->from('lespilotes@lespilotesconnectes.com')
            ->to($utilisateur->getEmail())
            ->subject('Votre compte a été banni')
            ->html("
                <p>Bonjour {$utilisateur->getPrenom()} {$utilisateur->getNom()},</p>
                <p>Nous vous informons que votre compte sur PRÉKAR a été banni.</p>
                <p><strong>Cause du bannissement :</strong> {$causeBannissement}</p>
                <p>Si vous pensez qu'il s'agit d'une erreur, veuillez contacter notre support à lespilotes@lespilotesconnectes.com.</p>
                <p>Cordialement,<br>L'équipe PRÉKAR</p>
            ");

        $mailer->send($email);

        // Suppression des données associées
        $proprietaire = $entityManager->getRepository(Proprietaire::class)->findOneBy(['utilisateur' => $utilisateur]);
        if ($proprietaire) {
            $offres = $proprietaire->getOffres();
            foreach ($offres as $offre) {
                $entityManager->remove($offre);
            }
            $entityManager->remove($proprietaire);
        }

        $emprunteur = $entityManager->getRepository(Emprunteur::class)->findOneBy(['utilisateur' => $utilisateur]);
        if ($emprunteur) {
            $locations = $entityManager->getRepository(Location::class)->findBy(['emprunteur' => $emprunteur]);
            foreach ($locations as $location) {
                $entityManager->remove($location);
            }
            $entityManager->remove($emprunteur);
        }

        // Supprimer l'utilisateur
        $entityManager->remove($utilisateur);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur a été banni et un email lui a été envoyé.");
        return $this->redirectToRoute('admin_utilisateurs');
    }


    #[Route('/offre/{id}/details', name: 'offre_detail', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function detail(Offre $offre): Response
    {
        $livraison = $offre->getLivraison();

        return $this->render('admin/offre_detail.html.twig', [
            'offre' => $offre,
            'livraison' => $livraison,
        ]);
    }

    #[Route('/offre/{id}/toggle-suspension', name: 'toggle_offre_suspension', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function toggleOfferSuspension(int $id, EntityManagerInterface $entityManager): Response
    {
        $offre = $entityManager->getRepository(Offre::class)->find($id);

        if (!$offre) {
            throw $this->createNotFoundException("Offre introuvable.");
        }

        $now = new \DateTime();
        if ($offre->getSuspendedUntil() && $offre->getSuspendedUntil() > $now) {
            // Réactiver l’offre
            $offre->setSuspendedUntil(null);
            $offre->setDisponibilite(true);
            $this->addFlash('success', "L’offre a été réactivée.");
        } else {
            // Suspendre l’offre
            $offre->setSuspendedUntil(new \DateTime('+30 days'));
            $offre->setDisponibilite(false);
            $this->addFlash('success', "L’offre a été suspendue.");
        }

        $entityManager->persist($offre);
        $entityManager->flush();

        return $this->redirectToRoute('admin_offres_utilisateur', ['id' => $offre->getProprietaire()->getUtilisateur()->getId()]);
    }

    #[Route('/locations', name: 'locations')]
    public function manageLocations(EntityManagerInterface $entityManager): Response
    {
        $locations = $entityManager->getRepository(Location::class)->findAll();

        return $this->render('admin/locations.html.twig', [
            'locations' => $locations,
        ]);
    }

    #[Route('/location/{id}/set-commission', name: 'set_commission', methods: ['POST'])]
    public function setCommission(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $location = $entityManager->getRepository(Location::class)->find($id);
        if (!$location) {
            throw $this->createNotFoundException("Location introuvable.");
        }

        $commission = $request->request->get('commission');
        if (!is_numeric($commission) || $commission < 0) {
            $this->addFlash('danger', "La commission doit être un nombre positif.");
            return $this->redirectToRoute('admin_locations');
        }

        $offre = $location->getOffre(); // Récupérer l'offre associée à la location

        if (!$offre) {
            $this->addFlash('danger', "Aucune offre associée à cette location.");
            return $this->redirectToRoute('admin_locations');
        }

        $offre->setCommission(floatval($commission));
        $entityManager->persist($offre);
        $entityManager->flush();


        $this->addFlash('success', "Commission mise à jour avec succès !");
        return $this->redirectToRoute('admin_locations');
    }

    #[Route('/admin/utilisateurs', name: 'admin_utilisateurs')]
    public function gestionUtilisateurs(
        ProprietaireRepository $proprietaireRepository,
        EmprunteurRepository $emprunteurRepository
    ): Response {
        $proprietaires = $proprietaireRepository->findAll();
        $emprunteurs = $emprunteurRepository->findAll();

        return $this->render('admin/utilisateurs.html.twig', [
            'proprietaires' => $proprietaires,
            'emprunteurs' => $emprunteurs,
        ]);
    }

    #[Route('/emprunteur/{id}/locations', name: 'locations_emprunteur')]
    public function locationsEmprunteur(int $id, EntityManagerInterface $entityManager): Response
    {
        $emprunteur = $entityManager->getRepository(Emprunteur::class)->find($id);

        if (!$emprunteur) {
            throw $this->createNotFoundException("Emprunteur introuvable.");
        }

        $locations = $entityManager->getRepository(Location::class)->findBy(['emprunteur' => $emprunteur]);

        return $this->render('admin/locations_emprunteur.html.twig', [
            'emprunteur' => $emprunteur,
            'locations' => $locations,
        ]);
    }

    #[Route('/admin/verser-montant/{id}', name: 'admin_verser_montant')]
    public function verserMontant(int $id, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);
        dump($utilisateur);

        if (!$utilisateur) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        $proprietaire = $entityManager->getRepository(Proprietaire::class)->findOneBy(['utilisateur' => $utilisateur]);
        dump($proprietaire);

        // Simuler le paiement en réinitialisant le montant à zéro
        $proprietaire->setRevenuTotal(0.0);
        $entityManager->persist($proprietaire);
        $entityManager->flush();

        $this->addFlash('success', 'Le montant a été versé au propriétaire.');
        return $this->redirectToRoute('admin_utilisateurs');
    }

    #[Route('/litiges', name: 'litiges')]
    #[IsGranted('ROLE_ADMIN')]
    public function manageLitiges(EntityManagerInterface $entityManager): Response
    {
        $litiges = $entityManager->getRepository(Litige::class)->findAll();
        return $this->render('admin/litiges.html.twig', [
            'litiges' => $litiges,
        ]);
    }

}
