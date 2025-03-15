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
    public function choisirAbonnement(): Response
    {
        return $this->render('abonnement/choisir.html.twig');
    }

    #[Route('/abonnement/ajouter', name: 'ajouter_abonnement', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function ajouterAbonnement(Request $request, EntityManagerInterface $entityManager): Response
{
    $utilisateur = $this->getUser();
    $type = $request->request->get('type');

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
            return $this->redirectToRoute('choisir_abonnement');
    }

    $abonnement = new Abonnement();
    $abonnement->setUtilisateur($utilisateur);
    $abonnement->setType($type);
    $abonnement->setPrix($prix);
    $abonnement->setDateDebut($dateDebut);
    $abonnement->setDateFin($dateFin);
    
    $entityManager->persist($abonnement);
    $entityManager->flush();

    // Ajout d'un message flash
    $this->addFlash('success', 'Votre abonnement a bien été pris en compte.');

    return $this->redirectToRoute('mes_abonnements');
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