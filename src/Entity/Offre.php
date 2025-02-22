<?php


namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 100)]
    private ?string $lieuGarage = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private ?float $prix = null;

    #[ORM\Column]
    private ?bool $disponibilite = null;
}

