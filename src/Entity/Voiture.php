<?php

namespace App\Entity;

use App\Repository\VoitureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoitureRepository::class)]
class Voiture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $marque = null;

    #[ORM\Column(length: 50)]
    private ?string $modele = null;

    #[ORM\Column(length: 20)]
    private ?string $immatriculation = null;

    #[ORM\Column]
    private ?int $annee = null;

    #[ORM\Column]
    private ?int $nombrePlaces = null;

    #[ORM\Column]
    private ?int $volumeCoffre = null;

    #[ORM\Column(length: 20)]
    private ?string $typeEssence = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}

