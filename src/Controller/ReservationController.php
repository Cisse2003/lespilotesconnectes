<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Offre;
use App\Entity\Emprunteur;
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
        $user = $this->getUser();
        if (!$offre->getDisponibilite()) {
            $this->addFlash('error', 'Cette offre n\'est plus disponible.');
            return $this->redirectToRoute('homepage');
        }

        if ($offre->getProprietaire()->getUtilisateur() === $user) {
            $this->addFlash('error', '❌ Vous ne pouvez pas réserver votre propre voiture.');
            return $this->redirectToRoute('homepage');
        }

        // Vérifier les locations existantes pour cette voiture
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

    // 🔥 Récupérer l'emprunteur associé à l'utilisateur
    $emprunteur = $entityManager->getRepository(Emprunteur::class)->findOneBy(['utilisateur' => $utilisateur]);

    // ✅ Si l'emprunteur n'existe pas
    if (!$emprunteur) {
        return new JsonResponse([
            'success' => false,
            'error' => '⚠️ Vous devez valider votre permis avant de réserver.'
        ]);
    }

    // 🔍 Vérifier si le permis est bien renseigné et valide
    if (!$emprunteur->getNumeroPermis() || !$emprunteur->getDateExpiration() || $emprunteur->getDateExpiration() < new \DateTime()) {
        return new JsonResponse([
            'success' => false,
            'error' => '⚠️ Votre permis est invalide ou expiré. Veuillez mettre à jour vos informations.'
        ]);
    }

    // Récupération des données de la requête
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

    $proprietaire = $offre->getProprietaire();
    if ($proprietaire && $proprietaire->getUtilisateur() === $utilisateur) {
    return new JsonResponse([
        'success' => false,
        'error' => '❌ Vous ne pouvez pas réserver votre propre voiture.'
    ]);
    }

    if ($dateDebut < $offre->getDateDebutDisponibilite() || $dateFin > $offre->getDateFinDisponibilite()) {
        return new JsonResponse([
            'success' => false,
            'error' => 'Les dates choisies ne correspondent pas à la période de disponibilité de ce véhicule.'
        ]);
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

    // Création de la réservation
    $location = new Location();
    $location->setDateDebut($dateDebut);
    $location->setDateFin($dateFin);
    $location->setOffre($offre);
    $location->setEmprunteur($emprunteur);

    $entityManager->persist($location);
    $entityManager->flush();

    return new JsonResponse(['success' => true, 'message' => 'Réservation effectuée avec succès.']);
}



#[Route('/mes-reservations', name: 'mes_reservations')]
public function mesReservations(EntityManagerInterface $entityManager, Security $security): Response
{
    $utilisateur = $security->getUser();
    
    if (!$utilisateur) {
        return $this->redirectToRoute('app_login'); // Redirige vers la connexion si l'utilisateur n'est pas connecté
    }

    // Récupérer l'emprunteur lié à l'utilisateur
    $emprunteur = $entityManager->getRepository(Emprunteur::class)->findOneBy(['utilisateur' => $utilisateur]);

    if (!$emprunteur) {
        return $this->render('reservation/mes_reservations.html.twig', ['reservations' => []]); // Affiche la page même si vide
    }

    // Récupérer les réservations de cet emprunteur
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
    $entityManager->persist($reservation); // ✅ Ajout du persist() pour enregistrer la modification
    $entityManager->flush(); // ✅ Appliquer les changements

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
    $entityManager->flush(); // ✅ Ajout de flush()

    return new JsonResponse(['success' => true]);
}




}
