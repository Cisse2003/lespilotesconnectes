<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\ManyToOne(targetEntity: Offre::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offre $offre = null;

    #[ORM\ManyToOne(targetEntity: Emprunteur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emprunteur $emprunteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}

