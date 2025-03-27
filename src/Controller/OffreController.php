<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Entity\Avis;
use App\Entity\Voiture;
use App\Entity\Proprietaire;
use App\Entity\Livraison;
use App\Form\OffreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class OffreController extends AbstractController
{
    #[Route('/offre/{id}/details', name: 'app_offre_detail', methods: ['GET'])]
    public function detail(Offre $offre): Response
    {
        $livraison = $offre->getLivraison();

        return $this->render('offre/detail.html.twig', [
            'offre' => $offre,
            'livraison' => $livraison
        ]);
    }

    #[Route('/offre/deposer', name: 'app_deposer_offre')]
    public function deposer(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $proprietaire = $user->getProprietaire();
        if (!$proprietaire) {
            $proprietaire = new Proprietaire();
            $proprietaire->setUtilisateur($user);
            $user->setProprietaire($proprietaire);
            $em->persist($proprietaire);
        }

        $voiture = new Voiture();
        $offre = new Offre();
        $offre->setDateCreation(new \DateTime());
        $offre->setVoiture($voiture);
        $offre->setProprietaire($proprietaire);

        // ✅ Créer une instance de livraison
        $livraison = new Livraison();
        $livraison->setOffre($offre);
        $offre->setLivraison($livraison);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Gestion des fichiers multiples
            $photos = $form->get('photos')->getData();
            $uploadedPhotos = [];

            if ($photos) {
                foreach ($photos as $photo) {
                    $fileName = md5(uniqid()) . '.' . $photo->guessExtension();

                    try {
                        $photo->move(
                            $this->getParameter('photos_directory'),
                            $fileName
                        );
                        $uploadedPhotos[] = $fileName;
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement : ' . $e->getMessage());
                    }
                }
                $offre->setPhotos($uploadedPhotos);
            }

            $em->persist($voiture);
            $em->persist($livraison);
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

        if ($offre->getProprietaire() !== $user->getProprietaire()) {
            throw $this->createAccessDeniedException("🚫 Vous n'avez pas le droit d'accéder à cette offre !");
        }

        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
        ]);
    }

    #[Route('/offres/{id}/supprimer', name: 'app_offre_delete', methods: ['POST', 'DELETE'])]
    public function delete(Offre $offre, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($offre->getProprietaire() !== $user->getProprietaire()) {
            throw $this->createAccessDeniedException("🚫 Vous n'avez pas le droit de supprimer cette offre !");
        }

        if ($this->isCsrfTokenValid('delete' . $offre->getId(), $request->request->get('_token'))) {
            // ✅ Supprimer les photos associées
            foreach ($offre->getPhotos() as $photo) {
                $filePath = $this->getParameter('photos_directory') . '/' . $photo;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $em->remove($offre);
            $em->flush();

            $this->addFlash('success', '✅ Offre supprimée avec succès !');
        } else {
            $this->addFlash('error', '❌ Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_offres');
    }

    #[Route('/offres/{id}/edit', name: 'app_offre_edit')]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($offre->getProprietaire() !== $this->getUser()->getProprietaire()) {
            throw $this->createAccessDeniedException("🚫 Vous n'avez pas le droit de modifier cette offre !");
        }

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($offre->getPhotos() as $photo) {
                $filePath = $this->getParameter('photos_directory') . '/' . $photo;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $offre->setPhotos([]);

            $photos = $form->get('photos')->getData();
            $photoPaths = [];

            if ($photos) {
                foreach ($photos as $photo) {
                    $fileName = md5(uniqid()) . '.' . $photo->guessExtension();
                    $photo->move(
                        $this->getParameter('photos_directory'),
                        $fileName
                    );
                    $photoPaths[] = $fileName;
                }
                $offre->setPhotos($photoPaths);
            }

            $em->flush();
            $this->addFlash('success', '✅ Offre modifiée avec succès !');
            return $this->redirectToRoute('app_offre_show', ['id' => $offre->getId()]);
        }

        return $this->render('offre/edit.html.twig', [
            'form' => $form->createView(),
            'offre' => $offre,
        ]);
    }

    #[Route('/offres/{id}/toggle-disponibilite', name: 'app_offre_toggle_disponibilite', methods: ['POST'])]
    public function toggleDisponibilite(Offre $offre, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || $offre->getProprietaire() !== $user->getProprietaire()) {
            throw $this->createAccessDeniedException("🚫 Vous n'avez pas le droit de modifier cette offre !");
        }

        // Basculer la disponibilité
        $offre->setDisponibilite(!$offre->getDisponibilite());
        $em->flush();

        return $this->json([
            'success' => true,
            'disponibilite' => $offre->getDisponibilite()
        ]);
    }

    // #[Route('/offre/{id}/avis', name: 'offre_avis', methods: ['GET', 'POST'])]
    // public function ajouterAvis(Request $request, Offre $offre): Response
    // {
    //     $avis = new Avis();
    //     $avis->setOffre($offre);
    //     $avis->setAuteur($this->getUser()); // Utilisateur connecté

    //     $form = $this->createForm(AvisType::class, $avis);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->persist($avis);
    //         $entityManager->flush();

    //         $this->addFlash('success', 'Votre avis a été enregistré !');
    //         return $this->redirectToRoute('offre_show', ['id' => $offre->getId()]);
    //     }

    //     return $this->render('offre/avis.html.twig', [
    //         'offre' => $offre,
    //         'form' => $form->createView(),
    //     ]);
    // }


    // #[Route('/offre/{id}', name: 'offre_show', methods: ['GET'])]
    // public function showAvis(Offre $offre): Response
    // {
    //     return $this->render('offre/show.html.twig', [
    //         'offre' => $offre,
    //     ]);
    // }




}