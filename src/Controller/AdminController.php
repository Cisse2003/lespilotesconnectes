<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Offre;
use App\Repository\OffreRepository;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(): Response
    {
        $admin = $this->getUser();

        if (!$admin) {
            throw $this->createAccessDeniedException("Accès refusé. Vous devez être administrateur.");
        }

        return $this->render('admin/admin_dashboard.html.twig', [
            'admin' => $admin
        ]);
    }

    #[Route('/utilisateur', name: 'utilisateurs')]
    public function manageUsers(UtilisateurRepository $repo): Response
    {
        $utilisateurs = $repo->findAll();

        $proprietaires = [];
        $emprunteurs = [];

        foreach ($utilisateurs as $utilisateur) {
            if ($utilisateur->getProprietaire()) {
                $proprietaires[] = $utilisateur;
            } else {
                $emprunteurs[] = $utilisateur;
            }
        }

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

        $proprietaire = $utilisateur->getProprietaire();
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
    public function deleteUser(int $id, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        $entityManager->remove($utilisateur);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur a été supprimé.");
        return $this->redirectToRoute('admin_utilisateurs');
    }


    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
        throw new \Exception('Ne pas oublier d\'activer le gestionnaire de déconnexion dans security.yaml');
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
}
