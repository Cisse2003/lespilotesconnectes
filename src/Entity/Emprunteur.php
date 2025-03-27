<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
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

    #[ORM\OneToOne(targetEntity: Utilisateur::class, inversedBy: "emprunteur", cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $nom = null;

    #[ORM\Column(length: 50, nullable: true)] // ✅ Permettre NULL en base de données
    #[Assert\Regex(
        pattern: "/^([A-Z0-9]{2}-[A-Z0-9]{2}-[A-Z0-9]{2}-[A-Z0-9]{2}-[A-Z0-9]{4}|[A-Z0-9]{12})$/",
        message: "Le numéro de permis doit respecter le format français (ex: AB-12-34-56-7890 ou 123456789012)."
    )]
    private ?string $numeroPermis = null;

    #[ORM\Column(type: "date", nullable: true)] // ✅ Permettre NULL en base de données
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

    public function setNumeroPermis(?string $numeroPermis): static // ✅ Accepte NULL
    {
        $this->numeroPermis = $numeroPermis;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): static // ✅ Accepte NULL
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }
}
