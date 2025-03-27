<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Offre;
use App\Entity\Emprunteur;
use App\Entity\Administrateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/reserver/{id}', name: 'reserver_voiture')]
    public function reserver(int $id, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        if (!$offre->getDisponibilite()) {
            $this->addFlash('error', 'Cette offre n\'est plus disponible.');
            return $this->redirectToRoute('homepage');
        }

        $locations = $entityManager->getRepository(Location::class)->findBy(['offre' => $offre]);

        $datesIndisponibles = [];
        foreach ($locations as $location) {
            $datesIndisponibles[] = [
                'debut' => $location->getDateDebut()->format('Y-m-d'),
                'fin' => $location->getDateFin()->format('Y-m-d')
            ];
        }

        return $this->render('reservation/reserver.html.twig', [
            'offre' => $offre,
            'datesIndisponibles' => json_encode($datesIndisponibles)
        ]);
    }

    #[Route('/valider', name: 'valider_reservation', methods: ['POST'])]
    public function validerReservation(Request $request, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $utilisateur = $security->getUser();
        if (!$utilisateur) {
            return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté pour réserver.']);
        }

        $emprunteur = $entityManager->getRepository(Emprunteur::class)->findOneBy(['utilisateur' => $utilisateur]);
        if (!$emprunteur) {
            return new JsonResponse([
                'success' => false,
                'error' => '⚠️ Vous devez valider votre permis avant de réserver.'
            ]);
        }

        if (!$emprunteur->getNumeroPermis() || !$emprunteur->getDateExpiration() || $emprunteur->getDateExpiration() < new \DateTime()) {
            return new JsonResponse([
                'success' => false,
                'error' => '⚠️ Votre permis est invalide ou expiré. Veuillez mettre à jour vos informations.'
            ]);
        }

        $data = json_decode($request->getContent(), true);
        $idOffre = $data['id_offre'] ?? null;
        $dateDebut = new \DateTime($data['date_debut'] ?? null);
        $dateFin = new \DateTime($data['date_fin'] ?? null);

        if (!$idOffre || !$dateDebut || !$dateFin) {
            return new JsonResponse(['success' => false, 'error' => 'Données invalides.']);
        }

        $offre = $entityManager->getRepository(Offre::class)->find($idOffre);
        if (!$offre) {
            return new JsonResponse(['success' => false, 'error' => 'Offre introuvable.']);
        }

        $locations = $entityManager->getRepository(Location::class)->findBy(['offre' => $offre]);
        foreach ($locations as $location) {
            if (($dateDebut >= $location->getDateDebut() && $dateDebut <= $location->getDateFin()) ||
                ($dateFin >= $location->getDateDebut() && $dateFin <= $location->getDateFin()) ||
                ($dateDebut <= $location->getDateDebut() && $dateFin >= $location->getDateFin())) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Ces dates ne sont pas disponibles.',
                    'datesIndisponibles' => array_map(function ($l) {
                        return [
                            'debut' => $l->getDateDebut()->format('Y-m-d'),
                            'fin' => $l->getDateFin()->format('Y-m-d')
                        ];
                    }, $locations)
                ]);
            }
        }

        // Calcul de la commission pour les administrateurs
        $admins = $entityManager->getRepository(Administrateur::class)->findAll();
        $commission = $offre->getCommission();

        if (!empty($admins) && $commission > 0) {
            $partParAdmin = $commission / count($admins);
            foreach ($admins as $admin) {
                $admin->ajouterCommission($partParAdmin);
                $entityManager->persist($admin);
            }
        }

        // Création de la réservation
        $location = new Location();
        $location->setDateDebut($dateDebut);
        $location->setDateFin($dateFin);
        $location->setOffre($offre);
        $location->setEmprunteur($emprunteur);

        // Mise à jour du revenuTotal du propriétaire
        $proprietaire = $offre->getProprietaire();
        $prixOffre = $offre->getPrix();
        $revenuActuel = $proprietaire->getRevenuTotal() ?? 0.0;
        $nouveauRevenu = $revenuActuel + $prixOffre;
        $nouveauRevenu = $nouveauRevenu * 0.90;
        $proprietaire->setRevenuTotal($nouveauRevenu);

        $entityManager->persist($location);
        $entityManager->persist($proprietaire);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Réservation effectuée avec succès.']);
    }

    #[Route('/mes-reservations', name: 'mes_reservations')]
    public function mesReservations(EntityManagerInterface $entityManager, Security $security): Response
    {
        $utilisateur = $security->getUser();

        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        $emprunteur = $entityManager->getRepository(Emprunteur::class)->findOneBy(['utilisateur' => $utilisateur]);
        if (!$emprunteur) {
            return $this->render('reservation/mes_reservations.html.twig', ['reservations' => []]);
        }

        $reservations = $entityManager->getRepository(Location::class)->findBy(['emprunteur' => $emprunteur]);

        return $this->render('reservation/mes_reservations.html.twig', [
            'reservations' => $reservations
        ]);
    }

    #[Route('/reservation/annuler/{id}', name: 'annuler_reservation', methods: ['POST'])]
    public function annulerReservation(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $reservation = $entityManager->getRepository(Location::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['success' => false, 'error' => 'Réservation introuvable.']);
        }

        $reservation->setStatut('Annulé');
        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/reservation/supprimer/{id}', name: 'supprimer_reservation', methods: ['POST'])]
    public function supprimerReservation(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $reservation = $entityManager->getRepository(Location::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['success' => false, 'error' => 'Réservation introuvable.']);
        }

        $entityManager->remove($reservation);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}