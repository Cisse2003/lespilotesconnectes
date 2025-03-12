<?php
// src/Controller/HomeController.php

namespace App\Controller;

use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(OffreRepository $offreRepository): Response
    {
        // Récupérer toutes les offres disponibles depuis la base de données
        $offres = $offreRepository->findAll();

        return $this->render('home.html.twig', [
            'offres' => $offres,
        ]);
    }
}
