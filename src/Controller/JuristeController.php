<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Litige;
use App\Repository\LitigeRepository;
use Symfony\Component\HttpFoundation\Request;

#[Route('/juriste', name: 'juriste_')]
class JuristeController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_JURISTE')]
    public function dashboard(): Response
    {
        $juriste = $this->getUser();

        if (!$juriste) {
            throw $this->createAccessDeniedException("Accès refusé. Vous devez être juriste.");
        }

        return $this->render('juriste/index.html.twig', [
            'juriste' => $juriste,
        ]);
    }

    #[Route('/litiges', name: 'litiges')]
    #[IsGranted('ROLE_JURISTE')]
    public function manageLitiges(LitigeRepository $litigeRepository): Response
    {
        $litiges = $litigeRepository->findAll();

        return $this->render('juriste/litiges.html.twig', [
            'litiges' => $litiges,
        ]);
    }

    #[Route('/litige/{id}/decision', name: 'decision_litige', methods: ['POST'])]
    #[IsGranted('ROLE_JURISTE')]
    public function decisionLitige(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $litige = $entityManager->getRepository(Litige::class)->find($id);

        if (!$litige) {
            throw $this->createNotFoundException("Litige introuvable.");
        }

        if ($litige->getStatut() === 'en cours') {
            $decision = $request->request->get('decision');  // Récupère la décision soumise

            // Enregistre la décision du juriste
            $litige->setDecisionJuriste($decision);

            // Change le statut du litige
            $litige->setStatut('traité');

            // Sauvegarde les modifications
            $entityManager->persist($litige);
            $entityManager->flush();

            $this->addFlash('success', "La décision a été validée. Le litige est maintenant traité.");
        }

        return $this->redirectToRoute('juriste_litiges');
    }

    // Méthode pour afficher les détails du litige
    #[Route('/litige/details/{id}', name: 'juriste_details_litige')]
    public function detailsLitige(int $id, EntityManagerInterface $entityManager): Response
    {
        $litige = $entityManager->getRepository(Litige::class)->find($id);

        if (!$litige) {
            throw $this->createNotFoundException('Le litige n\'a pas été trouvé');
        }

        return $this->render('juriste/details_litige.html.twig', [
            'litige' => $litige,
        ]);
    }


    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Symfony gère la déconnexion automatiquement
    }
}
