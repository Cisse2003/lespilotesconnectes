<?php

namespace App\Controller;

use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(OffreRepository $offreRepository): Response
    {
        // ✅ Récupération des offres complètes
        $offres = $offreRepository->findAll();

        // ✅ Récupération des marques et modèles distincts pour le formulaire de recherche
        $distinctValues = $offreRepository->createQueryBuilder('o')
            ->select('DISTINCT v.marque AS marque, v.modele AS modele')
            ->leftJoin('o.voiture', 'v')
            ->getQuery()
            ->getResult();

        // ✅ Envoi au template Twig
        return $this->render('home.html.twig', [
            'offres' => $offres,
            'distinctValues' => $distinctValues
        ]);
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, OffreRepository $offreRepository): Response
    {
        // ✅ Récupération des filtres de la requête GET
        $year = $request->query->get('year');
        $make = $request->query->get('make');
        $model = $request->query->get('model');
        $price = $request->query->get('price');
        $places = $request->query->get('places');
        $livraison = $request->query->get('livraison');

        // ✅ Construction de la requête dynamique
        $queryBuilder = $offreRepository->createQueryBuilder('o')
            ->leftJoin('o.voiture', 'v')
            ->leftJoin('o.livraison', 'l', 'WITH', 'l.offre = o.id');

        // ✅ Recherche par année (type INT)
        if (!empty($year) && is_numeric($year)) {
            $queryBuilder->andWhere('v.annee = :year')
                ->setParameter('year', intval($year));
        }

        // ✅ Recherche par marque (insensible à la casse)
        if (!empty($make)) {
            $queryBuilder->andWhere('LOWER(v.marque) LIKE :make')
                ->setParameter('make', '%' . strtolower($make) . '%');
        }

        // ✅ Recherche par modèle (insensible à la casse)
        if (!empty($model)) {
            $queryBuilder->andWhere('LOWER(v.modele) LIKE :model')
                ->setParameter('model', '%' . strtolower($model) . '%');
        }

        // ✅ Recherche par prix (type FLOAT)
        if (!empty($price) && is_numeric($price)) {
            $queryBuilder->andWhere('o.prix <= :price')
                ->setParameter('price', floatval($price));
        }

        // ✅ Recherche par nombre de places (type INT)
        if (!empty($places) && is_numeric($places)) {
            $queryBuilder->andWhere('v.nombrePlaces = :places')
                ->setParameter('places', intval($places));
        }

        // ✅ Recherche par disponibilité de la livraison (type BOOL)
        if ($livraison !== null && $livraison !== '') {
            $queryBuilder->andWhere('COALESCE(l.disponibilite, 0) = :livraison')
                ->setParameter('livraison', $livraison === '1' ? true : false);
        }

        // ✅ Debug SQL dans le profiler Symfony (pour vérifier la requête générée)
        dump($queryBuilder->getQuery()->getSQL());
        dump($queryBuilder->getParameters());

        // ✅ Exécution de la requête
        $offres = $queryBuilder->getQuery()->getResult();

        // ✅ Récupération des marques et modèles distincts pour le formulaire de recherche
        $distinctValues = $offreRepository->createQueryBuilder('o')
            ->select('DISTINCT v.marque AS marque, v.modele AS modele')
            ->leftJoin('o.voiture', 'v')
            ->getQuery()
            ->getResult();

        // ✅ Envoi au template Twig
        return $this->render('home.html.twig', [
            'offres' => $offres,
            'distinctValues' => $distinctValues
        ]);
    }
}
