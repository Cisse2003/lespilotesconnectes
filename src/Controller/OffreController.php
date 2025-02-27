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
        
        // Création du formulaire à partir du type OffreType
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification du format de l'immatriculation (format français : XX-123-XX)
            $immatriculation = $voiture->getImmatriculation();
            $immatriculation = strtoupper($immatriculation);
            if (!preg_match('/^[A-Z]{2}-\d{3}-[A-Z]{2}$/', $immatriculation)) {
                $this->addFlash('error', "L'immatriculation n'est pas valide. Format attendu : XX-123-XX");
                return $this->render('offre/deposer.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            
            // Traitement des fichiers uploadés (photos) si nécessaire
            $photos = $form->get('photos')->getData();
            if ($photos) {
                foreach ($photos as $photo) {
                    // Exemple de traitement : générer un nom unique et déplacer le fichier
                    // $nomPhoto = md5(uniqid()) . '.' . $photo->guessExtension();
                    // $photo->move($this->getParameter('photos_directory'), $nomPhoto);
                    // Vous pouvez sauvegarder le chemin dans une entité dédiée.
                }
            }
            
            // Persister la voiture et l'offre
            $em->persist($voiture);
            $em->persist($offre);
            $em->flush();
            
            $this->addFlash('success', 'Offre déposée avec succès !');
            return $this->redirectToRoute('homepage');
        }
        
        return $this->render('offre/deposer.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

