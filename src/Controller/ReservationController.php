<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Offre;
use App\Entity\Litige;
use App\Entity\Emprunteur;
use App\Entity\Administrateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
        if ($proprietaire && $proprietaire->getUtilisateur() === $utilisateur){
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

    #[Route('/signaler-litige/{id}', name: 'signaler_litige')]
    public function signalerLitige(int $id, EntityManagerInterface $entityManager): Response
    {
        $location = $entityManager->getRepository(Location::class)->find($id);
        if (!$location) {
            throw $this->createNotFoundException('Réservation introuvable.');
        }

        return $this->render('reservation/signaler_litige.html.twig', [
            'location' => $location,
        ]);
    }

    #[Route('/soumettre-litige/{id}', name: 'soumettre_litige', methods: ['POST'])]
    public function soumettreLitige(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        MailerInterface $mailer
    ): Response {
        $location = $entityManager->getRepository(Location::class)->find($id);
        if (!$location) {
            throw $this->createNotFoundException('Réservation introuvable.');
        }

        $utilisateur = $security->getUser();
        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        $nature = $request->request->get('nature');
        $description = $request->request->get('description');
        $images = $request->files->get('images'); // Tableau de fichiers (peut contenir plusieurs entrées)
        $documents = $request->files->get('documents'); // Tableau de fichiers

        // Création du litige
        $litige = new Litige();
        $litige->setLocation($location);
        $litige->setProprietaire($location->getOffre()->getProprietaire()->getUtilisateur());
        $litige->setEmprunteur($utilisateur);
        $litige->setDescription($nature . ' - ' . $description);
        $litige->setStatut('en cours');

        // Gestion des fichiers
        $filePaths = [];
        $fileNamesForEmail = []; // Pour l’email

        // Traitement des images
        if ($images) {
            foreach ($images as $imageGroup) { // Boucle sur chaque champ input
                if (is_array($imageGroup)) { // Si plusieurs fichiers dans un même input
                    foreach ($imageGroup as $image) {
                        if ($image) { // Vérifie que le fichier est valide
                            $fileName = uniqid('img_') . '.' . $image->guessExtension();
                            $image->move($this->getParameter('uploads_directory'), $fileName);
                            $filePaths[] = '/uploads/' . $fileName; // Chemin relatif pour stockage
                            $fileNamesForEmail[] = $fileName; // Nom pour l’email
                        }
                    }
                } elseif ($imageGroup) { // Cas d’un seul fichier dans un input
                    $fileName = uniqid('img_') . '.' . $imageGroup->guessExtension();
                    $imageGroup->move($this->getParameter('uploads_directory'), $fileName);
                    $filePaths[] = '/uploads/' . $fileName;
                    $fileNamesForEmail[] = $fileName;
                }
            }
        }

        // Traitement des documents
        if ($documents) {
            foreach ($documents as $docGroup) {
                if (is_array($docGroup)) {
                    foreach ($docGroup as $document) {
                        if ($document) {
                            $fileName = uniqid('doc_') . '.' . $document->guessExtension();
                            $document->move($this->getParameter('uploads_directory'), $fileName);
                            $filePaths[] = '/uploads/' . $fileName;
                            $fileNamesForEmail[] = $fileName;
                        }
                    }
                } elseif ($docGroup) {
                    $fileName = uniqid('doc_') . '.' . $docGroup->guessExtension();
                    $docGroup->move($this->getParameter('uploads_directory'), $fileName);
                    $filePaths[] = '/uploads/' . $fileName;
                    $fileNamesForEmail[] = $fileName;
                }
            }
        }

        // Stockage des chemins dans l’entité
        $litige->setFichiers($filePaths);

        $entityManager->persist($litige);
        $entityManager->flush();

        // Préparation de la liste des fichiers pour l’email
        $fileListHtml = '';
        if (!empty($fileNamesForEmail)) {
            $fileListHtml = '<p><strong>Fichiers joints :</strong></p><ul>';
            foreach ($fileNamesForEmail as $fileName) {
                $fileListHtml .= "<li>{$fileName}</li>";
            }
            $fileListHtml .= '</ul>';
        }

        // Envoi de l’email d’accusé de réception
        $email = (new Email())
            ->from('support@lespilotesconnectes.com')
            ->to($utilisateur->getEmail())
            ->subject('Accusé de réception de votre signalement de litige')
            ->html("
                <p>Bonjour {$utilisateur->getPrenom()} {$utilisateur->getNom()},</p>
                <p>Nous avons bien reçu votre signalement de litige concernant la réservation du véhicule 
                {$location->getOffre()->getVoiture()->getMarque()} {$location->getOffre()->getVoiture()->getModele()}.</p>
                <p><strong>Nature :</strong> {$nature}</p>
                <p><strong>Description :</strong> {$description}</p>
                {$fileListHtml}
                <p>Votre demande est en cours de traitement. Nous vous tiendrons informé(e) de son avancement.</p>
                <p>Cordialement,<br>L’équipe Les Pilotes Connectés</p>
            ");

        $mailer->send($email);

        $this->addFlash('success', 'Votre litige a été signalé avec succès. Un email de confirmation vous a été envoyé.');
        return $this->redirectToRoute('mes_reservations');
    }
}