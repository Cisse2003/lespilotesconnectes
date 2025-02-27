<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Entity\Voiture;
use App\Entity\Livraison;
use App\Entity\Utilisateur;
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
        // ✅ Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour déposer une offre.');
            return $this->redirectToRoute('app_login'); // Assurez-vous que 'app_login' est bien votre route de connexion
        }

        // ✅ Vérifier que l'utilisateur est bien une instance de Utilisateur
        if (!$user instanceof Utilisateur) {
            throw new \Exception("Erreur : L'utilisateur connecté n'est pas un utilisateur valide.");
        }

        // ✅ Création de l'offre et association avec l'utilisateur
        $offre = new Offre();
        $offre->setDateCreation(new \DateTime());
        $offre->setProprietaire($user);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Gestion des données de la Voiture
            $voiture = new Voiture();
            $voiture->setMarque($form->get('marque')->getData());
            $voiture->setModele($form->get('modele')->getData());
            $voiture->setImmatriculation($form->get('immatriculation')->getData());
            $voiture->setAnnee($form->get('annee')->getData());
            $voiture->setNombrePlaces($form->get('nombrePlaces')->getData());
            $voiture->setVolumeCoffre($form->get('volumeCoffre')->getData());
            $voiture->setTypeEssence($form->get('typeEssence')->getData());

            // Persister la voiture
            $em->persist($voiture);
            $em->flush();

            // Associer la voiture à l'offre
            $offre->setVoiture($voiture);

            // ✅ Gestion des données de Livraison (si renseignées)
            $livraisonTarifs = $form->get('livraisonTarifs')->getData();
            $livraisonDisponibilite = $form->get('livraisonDisponibilite')->getData();

            if ($livraisonTarifs !== null) { // Vérification pour éviter les erreurs
                $livraison = new Livraison();
                $livraison->setTarifs($livraisonTarifs);
                $livraison->setDisponibilite($livraisonDisponibilite);
                $livraison->setOffre($offre);

                $em->persist($livraison);
            }

            // ✅ Gestion de l'upload des photos
            $photos = $form->get('photos')->getData();
            if ($photos) {
                $photosPaths = [];
                $uploadDirectory = $this->getParameter('photos_directory');

                foreach ($photos as $photo) {
                    $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                    $newFilename = uniqid() . '.' . $photo->guessExtension();

                    try {
                        $photo->move($uploadDirectory, $newFilename);
                        $photosPaths[] = $newFilename;
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload d\'une photo.');
                    }
                }
            }

            // ✅ Persister l'offre
            $em->persist($offre);
            $em->flush();

            $this->addFlash('success', 'Votre offre a été déposée avec succès !');
            return $this->redirectToRoute('app_deposer_offre');
        }

        return $this->render('offre/deposer.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

