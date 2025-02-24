<?php


// src/Controller/OffreController.php

namespace App\Controller;

use App\Entity\Offre;
use App\Form\OffreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class OffreController extends AbstractController
{
    #[Route('/deposer-offre', name: 'app_deposer_offre')]
    public function deposer(
        Request $request, 
        EntityManagerInterface $em, 
        Security $security, 
        SluggerInterface $slugger
    ): Response {
        // Vérifier que l'utilisateur est connecté
        $utilisateur = $security->getUser();
        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        $offre = new Offre();
        $offre->setDateCreation(new \DateTime());
        
        // Création du formulaire
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que l'utilisateur est un propriétaire
            $proprietaire = $utilisateur->getProprietaire();
            if (!$proprietaire) {
                $this->addFlash('danger', 'Vous devez être un propriétaire pour déposer une offre.');
                return $this->redirectToRoute('app_home');
            }

            // Associer l'offre au propriétaire
            $offre->setProprietaire($proprietaire);

            // Gestion de la livraison si l'utilisateur a coché "Proposer une livraison"
            if ($form->get('proposeLivraison')->getData()) {
                $livraison = $form->get('livraison')->getData();
                if ($livraison) {
                    $livraison->setOffre($offre);
                    $em->persist($livraison);
                }
            }

            // Gestion de l'upload des images
            $imageFiles = $form->get('image')->getData();
            if ($imageFiles) {
                $uploadedFilenames = [];
                foreach ($imageFiles as $imageFile) {
                    $newFilename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '-' . uniqid() . '.' . $imageFile->guessExtension();
                    try {
                        $imageFile->move($this->getParameter('images_directory'), $newFilename);
                        $uploadedFilenames[] = $newFilename;
                    } catch (FileException $e) {
                        $this->addFlash('danger', 'Erreur lors du téléchargement des images.');
                    }
                }
                // Stocker les images au format JSON dans l'entité Offre
                $offre->setImage(json_encode($uploadedFilenames));
            }

            // Enregistrer l'offre en base
            $em->persist($offre);
            $em->flush();

            $this->addFlash('success', 'Votre offre a bien été enregistrée !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('offre/deposer.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}


