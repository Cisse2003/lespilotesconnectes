<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Avis;


#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateDebutDisponibilite = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateFinDisponibilite = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $lieuGarage = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?string $prix = '0.00';
    #[ORM\Column]
    private ?bool $disponibilite = true;

    #[ORM\ManyToOne(targetEntity: Voiture::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Voiture $voiture = null;

    #[ORM\ManyToOne(targetEntity: Proprietaire::class, inversedBy: 'offres')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Proprietaire $proprietaire = null;


    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $photos = [];

    #[ORM\OneToOne(targetEntity: Livraison::class, mappedBy: 'offre', cascade: ['persist', 'remove'])]
    private ?Livraison $livraison = null;

    // Nouveau champ pour la suspension
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $suspendedUntil = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $date): self
    {
        $this->dateCreation = $date;
        return $this;
    }

    public function getDateDebutDisponibilite(): ?\DateTimeInterface
    {
        return $this->dateDebutDisponibilite;
    }

    public function setDateDebutDisponibilite(?\DateTimeInterface $dateDebutDisponibilite): self
    {
        $this->dateDebutDisponibilite = $dateDebutDisponibilite;
        return $this;
    }

    public function getDateFinDisponibilite(): ?\DateTimeInterface
    {
        return $this->dateFinDisponibilite;
    }

    public function setDateFinDisponibilite(?\DateTimeInterface $dateFinDisponibilite): self
    {
        $this->dateFinDisponibilite = $dateFinDisponibilite;
        return $this;
    }

    public function getLieuGarage(): ?string
    {
        return $this->lieuGarage;
    }

    public function setLieuGarage(string $lieuGarage): self
    {
        $this->lieuGarage = $lieuGarage;
        return $this;
    }

    public function getPrix(): ?string
{
    return $this->prix;
}

public function setPrix(string $prix): self
{
    $this->prix = $prix;
    return $this;
}

    public function getDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getVoiture(): ?Voiture
    {
        return $this->voiture;
    }

    public function setVoiture(Voiture $voiture): self
    {
        $this->voiture = $voiture;
        return $this;
    }

    public function getProprietaire(): ?Proprietaire
    {
        return $this->proprietaire;
    }

    public function setProprietaire(Proprietaire $proprietaire): self
    {
        $this->proprietaire = $proprietaire;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): self
    {
        $this->photos = $photos;
        return $this;
    }

    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): self
    {
        $this->livraison = $livraison;
        if ($livraison !== null) {
            $livraison->setOffre($this);
        }
        return $this;
    }

    // Getter et Setter pour suspendedUntil
    public function getSuspendedUntil(): ?\DateTimeInterface
    {
        return $this->suspendedUntil;
    }

    public function setSuspendedUntil(?\DateTimeInterface $suspendedUntil): self
    {
        $this->suspendedUntil = $suspendedUntil;
        return $this;
    }

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $commission = '0.10';

    public function getCommission(): float
{
    return floatval($this->prix) * floatval($this->commission);
}

public function setCommission(?float $commission): self
{
    $this->commission = number_format($commission / 100, 2, '.', '');
    return $this;
}


    public function getRevenuProprietaire(): float
    {
        return $this->prix * (1 - $this->commission);
    }

    #[ORM\OneToMany(mappedBy: 'offre', targetEntity: Avis::class, cascade: ['remove'])]
private Collection $avis;

public function __construct()
{
    $this->avis = new ArrayCollection();
}

public function getAvis(): Collection
{
    return $this->avis;
}

public function addAvis(Avis $avis): self
{
    if (!$this->avis->contains($avis)) {
        $this->avis[] = $avis;
        $avis->setOffre($this);
    }

    return $this;
}

public function removeAvis(Avis $avis): self
{
    if ($this->avis->removeElement($avis)) {
        if ($avis->getOffre() === $this) {
            $avis->setOffre(null);
        }
    }

    return $this;
}




}