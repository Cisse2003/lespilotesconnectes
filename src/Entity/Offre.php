<?php


namespace App\Entity;
use App\Entity\Utilisateur;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateDebutDisponibilite = null; // ✅ Ajout

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateFinDisponibilite = null; // ✅ Ajout

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $lieuGarage = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $prix = null;

    #[ORM\Column]
    private ?bool $disponibilite = true;

    #[ORM\ManyToOne(targetEntity: Voiture::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Voiture $voiture = null;

    #[ORM\ManyToOne(targetEntity: Proprietaire::class)]
    #[ORM\JoinColumn(nullable: false)]
   private ?Utilisateur $proprietaire = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    // 🚀 GETTERS & SETTERS pour la disponibilité
    public function getDateDebutDisponibilite(): ?\DateTimeInterface
    {
        return $this->dateDebutDisponibilite;
    }

    public function setDateDebutDisponibilite(?\DateTimeInterface $date): self
    {
        $this->dateDebutDisponibilite = $date;
        return $this;
    }

    public function getDateFinDisponibilite(): ?\DateTimeInterface
    {
        return $this->dateFinDisponibilite;
    }

    public function setDateFinDisponibilite(?\DateTimeInterface $date): self
    {
        $this->dateFinDisponibilite = $date;
        return $this;
    }

    // ✅ Ajout des Getters et Setters existants
    public function getId(): ?int { return $this->id; }
    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(\DateTimeInterface $date): self { $this->dateCreation = $date; return $this; }
    public function getLieuGarage(): ?string { return $this->lieuGarage; }
    public function setLieuGarage(string $lieuGarage): self { $this->lieuGarage = $lieuGarage; return $this; }
    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(float $prix): self { $this->prix = $prix; return $this; }
    public function getDisponibilite(): ?bool { return $this->disponibilite; }
    public function setDisponibilite(bool $disponibilite): self { $this->disponibilite = $disponibilite; return $this; }
    public function getVoiture(): ?Voiture { return $this->voiture; }
    public function setVoiture(Voiture $voiture): self { $this->voiture = $voiture; return $this; }
public function getProprietaire(): ?Utilisateur
{
    return $this->proprietaire;
}
    public function setProprietaire(Utilisateur $proprietaire): self
{
    $this->proprietaire = $proprietaire;
    return $this;
}
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
}



