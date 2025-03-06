<?php

namespace App\Entity;

use App\Repository\EmprunteurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmprunteurRepository::class)]
class Emprunteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le numéro de permis est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[A-Z0-9]{2}-[A-Z0-9]{2}-[A-Z0-9]{2}-[A-Z0-9]{2}-[A-Z0-9]{4}$/",
        message: "Le numéro de permis doit respecter le format français (ex: AB-12-34-56-7890)."
    )]
    private ?string $numeroPermis = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: "La date d'expiration est obligatoire.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date d'expiration ne peut pas être passée.")]
    private ?\DateTimeInterface $dateExpiration = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getNumeroPermis(): ?string
    {
        return $this->numeroPermis;
    }

    public function setNumeroPermis(string $numeroPermis): static
    {
        $this->numeroPermis = $numeroPermis;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeInterface $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }
}
