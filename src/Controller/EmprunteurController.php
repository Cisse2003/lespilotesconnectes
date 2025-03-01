<?php

namespace App\Controller;

use App\Entity\Emprunteur;
use App\Form\EmprunteurFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmprunteurController extends AbstractController
{
    #[Route('/ajouter-permis', name: 'ajouter_permis')]
    public function ajouterPermis(Request $request, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $this->getUser();
        if (!$utilisateur) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un permis.');
            return $this->redirectToRoute('app_login');
        }

        $emprunteur = $entityManager->getRepository(Emprunteur::class)
            ->createQueryBuilder('e')
            ->join('e.utilisateur', 'u')
            ->addSelect('u')
            ->where('e.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();

        if ($emprunteur && $emprunteur->getDateExpiration() > new \DateTime()) {
            return $this->render('emprunteur/permis_valide.html.twig', [
                'emprunteur' => $emprunteur,
            ]);
        }

        if (!$emprunteur) {
            $emprunteur = new Emprunteur();
            $emprunteur->setUtilisateur($utilisateur);
        }

        $form = $this->createForm(EmprunteurFormType::class, $emprunteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($emprunteur);
            $entityManager->flush();

            $this->addFlash('success', 'Permis ajouté avec succès !');
            return $this->redirectToRoute('ajouter_permis');
        }

        return $this->render('emprunteur/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer-permis/{id}', name: 'supprimer_permis', methods: ['POST'])]
    public function supprimerPermis(int $id, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $this->getUser();
        if (!$utilisateur) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un permis.');
            return $this->redirectToRoute('app_login');
        }

        $emprunteur = $entityManager->getRepository(Emprunteur::class)->find($id);

        if ($emprunteur && $emprunteur->getUtilisateur() === $utilisateur) {
            $entityManager->remove($emprunteur);
            $entityManager->flush();

            $this->addFlash('success', 'Permis supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Permis introuvable ou non associé à votre compte.');
        }

        return $this->redirectToRoute('ajouter_permis');
    }
}
