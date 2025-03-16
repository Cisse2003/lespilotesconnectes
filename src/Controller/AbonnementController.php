<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Repository\AbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AbonnementController extends AbstractController
{
    #[Route('/abonnement', name: 'choisir_abonnement')]
public function choisirAbonnement(AbonnementRepository $abonnementRepository): Response
{
    // Récupérer l'utilisateur connecté
    $utilisateur = $this->getUser();

    // Vérifier si l'utilisateur est authentifié
    if (!$utilisateur) {
        throw $this->createAccessDeniedException("Vous devez être connecté pour voir vos abonnements.");
    }

    // Récupérer les abonnements existants de l'utilisateur
    $abonnements = $abonnementRepository->findBy(['utilisateur' => $utilisateur]);

    // Extraire uniquement les types d'abonnements déjà souscrits (ex: ['journalier', 'mensuel'])
    $typesAbonnementsExistants = array_map(fn($abonnement) => $abonnement->getType(), $abonnements);

    return $this->render('abonnement/choisir.html.twig', [
        'abonnements_existants' => $typesAbonnementsExistants,
    ]);
}


    #[Route('/abonnement/ajouter', name: 'ajouter_abonnement', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function ajouterAbonnement(Request $request, EntityManagerInterface $entityManager): Response
{
    $utilisateur = $this->getUser();
    
    $data = json_decode($request->getContent(), true);
    $type = $data['type'] ?? null;

    if (!$type) {
        return $this->json(['success' => false, 'error' => 'Aucun type d’abonnement sélectionné.'], 400);
    }

    $dateDebut = new \DateTime();
    $dateFin = clone $dateDebut;

    switch ($type) {
        case 'journalier':
            $prix = 10.00;
            $dateFin->modify('+1 day');
            break;
        case 'mensuel':
            $prix = 50.00;
            $dateFin->modify('+1 month');
            break;
        case 'annuel':
            $prix = 500.00;
            $dateFin->modify('+1 year');
            break;
        default:
            return $this->json(['success' => false, 'error' => 'Type d’abonnement invalide.'], 400);
    }

    $abonnement = new Abonnement();
    $abonnement->setUtilisateur($utilisateur);
    $abonnement->setType($type);
    $abonnement->setPrix($prix);
    $abonnement->setDateDebut($dateDebut);
    $abonnement->setDateFin($dateFin);

    $entityManager->persist($abonnement);
    $entityManager->flush();

    return $this->json(['success' => true]);
}


#[Route('/abonnement/payer', name: 'payer_abonnement')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function payerAbonnement(Request $request): Response
{
    $type = $request->request->get('type');

    if (!$type) {
        $this->addFlash('error', 'Veuillez sélectionner un abonnement.');
        return $this->redirectToRoute('choisir_abonnement');
    }

    return $this->render('abonnement/payer.html.twig', [
        'type' => $type
    ]);
}




    #[Route('/abonnement/mes-abonnements', name: 'mes_abonnements')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function mesAbonnements(AbonnementRepository $abonnementRepository): Response
    {
        $abonnements = $abonnementRepository->findBy(['utilisateur' => $this->getUser()]);
        return $this->render('abonnement/mes_abonnements.html.twig', [
            'abonnements' => $abonnements,
        ]);
    }

    #[Route('/abonnement/supprimer/{id}', name: 'supprimer_abonnement', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function supprimerAbonnement(Abonnement $abonnement, EntityManagerInterface $entityManager): Response
    {
        if ($abonnement->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de supprimer cet abonnement.");
        }

        $entityManager->remove($abonnement);
        $entityManager->flush();

        return $this->redirectToRoute('mes_abonnements');
    }
}