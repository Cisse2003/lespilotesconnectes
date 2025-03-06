<?php
// src/Controller/OffreController.php
namespace App\Controller;

use App\Entity\Offre;
use App\Entity\Voiture;
use App\Entity\Proprietaire;
use App\Form\OffreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OffreController extends AbstractController
{
    #[Route('/offre/deposer', name: 'app_deposer_offre')]
    public function deposer(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier si l'utilisateur est déjà propriétaire, sinon le créer
        $proprietaire = $user->getProprietaire();
        if (!$proprietaire) {
            $proprietaire = new Proprietaire();
            $proprietaire->setUtilisateur($user);
            $user->setProprietaire($proprietaire);
            $em->persist($proprietaire);
        }
        
        // Création d'une nouvelle voiture et d'une nouvelle offre
        $voiture = new Voiture();
        $offre = new Offre();
        $offre->setDateCreation(new \DateTime());
        $offre->setVoiture($voiture);
        $offre->setProprietaire($proprietaire);
        
        // Création du formulaire
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister la voiture et l'offre
            $em->persist($voiture);
            $em->persist($offre);
            $em->flush();
            
            $this->addFlash('success', 'Offre déposée avec succès !');
            return $this->redirectToRoute('app_offres');
        }
        
        return $this->render('offre/deposer.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/offres', name: 'app_offres')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer les offres du propriétaire connecté
        $proprietaire = $user->getProprietaire();
        $offres = $proprietaire ? $proprietaire->getOffres() : [];
        
        return $this->render('offre/index.html.twig', [
            'offres' => $offres,
        ]);
    }

    #[Route('/offres/{id}', name: 'app_offre_show')]
    public function show(Offre $offre): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier si l'offre appartient à l'utilisateur connecté
        if ($offre->getProprietaire() !== $user->getProprietaire()) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette offre.");
        }
        
        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
        ]);
    }

#[Route('/offres/{id}/edit', name: 'app_offre_edit')]
public function edit(Request $request, Offre $offre, EntityManagerInterface $em): Response
{
    // Vérifier si l'utilisateur est bien le propriétaire de l'offre
    if ($offre->getProprietaire() !== $this->getUser()->getProprietaire()) {
        throw $this->createAccessDeniedException("🚫 Vous n'avez pas le droit de modifier cette offre !");
    }

    // Créer le formulaire pré-rempli avec l'offre existante
    $form = $this->createForm(OffreType::class, $offre);
    $form->handleRequest($request);

    // Si le formulaire est soumis et valide, on enregistre les modifications
    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();  // Enregistrer les modifications dans la base de données
        $this->addFlash('success', '✅ Offre modifiée avec succès !');
        return $this->redirectToRoute('app_offre_show', ['id' => $offre->getId()]);
    }

    // Afficher le formulaire pour l'édition de l'offre
    return $this->render('offre/edit.html.twig', [
        'form' => $form->createView(),
        'offre' => $offre,
    ]);
}


#[Route('/offres/{id}/toggle-disponibilite', name: 'app_offre_toggle_disponibilite', methods: ['POST'])]
public function toggleDisponibilite(Offre $offre, EntityManagerInterface $em): Response
{
    // Vérifier si l'utilisateur est bien le propriétaire de l'offre
    if ($offre->getProprietaire() !== $this->getUser()->getProprietaire()) {
        return $this->json(['success' => false, 'message' => "Accès refusé"], Response::HTTP_FORBIDDEN);
    }

    // Inverser la disponibilité
    $offre->setDisponibilite(!$offre->getDisponibilite());

    // Sauvegarder en base de données
    $em->flush();

    return $this->json([
        'success' => true,
        'newDisponibilite' => $offre->getDisponibilite(),
    ]);
}


#[Route('/offres/{id}/supprimer', name: 'app_offre_delete', methods: ['POST', 'DELETE'])]
public function delete(Offre $offre, EntityManagerInterface $em, Request $request): Response
{
    // Vérifier si l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Vérifier si l'utilisateur est bien le propriétaire de l'offre
    if ($offre->getProprietaire() !== $user->getProprietaire()) {
        throw $this->createAccessDeniedException("Vous n'avez pas le droit de supprimer cette offre.");
    }

    // Vérifier la validité du token CSRF
    if ($this->isCsrfTokenValid('delete' . $offre->getId(), $request->request->get('_token'))) {
        $em->remove($offre);
        $em->flush();
        $this->addFlash('success', 'Offre supprimée avec succès.');
    } else {
        $this->addFlash('error', 'Token CSRF invalide, suppression annulée.');
    }

    return $this->redirectToRoute('app_offres');
}


}

