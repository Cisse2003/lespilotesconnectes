<?php

namespace App\Entity;

use App\Repository\LitigeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LitigeRepository::class)]
class Litige
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $proprietaire = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $emprunteur = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateSignalement = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $fichiers = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $decisionJuriste = null;  // Nouveau champ

    public function __construct()
    {
        $this->dateSignalement = new \DateTime();
        $this->statut = 'en cours';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getProprietaire(): ?Utilisateur
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?Utilisateur $proprietaire): self
    {
        $this->proprietaire = $proprietaire;
        return $this;
    }

    public function getEmprunteur(): ?Utilisateur
    {
        return $this->emprunteur;
    }

    public function setEmprunteur(?Utilisateur $emprunteur): self
    {
        $this->emprunteur = $emprunteur;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateSignalement(): ?\DateTimeInterface
    {
        return $this->dateSignalement;
    }

    public function setDateSignalement(\DateTimeInterface $dateSignalement): self
    {
        $this->dateSignalement = $dateSignalement;
        return $this;
    }

    public function getFichiers(): ?array
    {
        return $this->fichiers;
    }

    public function setFichiers(?array $fichiers): self
    {
        $this->fichiers = $fichiers;
        return $this;
    }

    public function getDecisionJuriste(): ?string
    {
        return $this->decisionJuriste;
    }

    public function setDecisionJuriste(?string $decisionJuriste): self
    {
        $this->decisionJuriste = $decisionJuriste;
        return $this;
    }
}
