<?php

namespace App\Repository;

use App\Entity\Juriste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Juriste|null find($id, $lockMode = null, $lockVersion = null)
 * @method Juriste|null findOneBy(array $criteria, array $orderBy = null)
 * @method Juriste[]    findAll()
 * @method Juriste[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JuristeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Juriste::class);
    }

    // Exemple d'une méthode personnalisée pour obtenir tous les juristes actifs
    public function findActiveJuristes()
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.statut = :statut')
            ->setParameter('statut', 'actif')
            ->orderBy('j.dateCreation', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
