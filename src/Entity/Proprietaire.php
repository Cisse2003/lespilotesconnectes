<?php

namespace App\Entity;

use App\Repository\ProprietaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProprietaireRepository::class)]
class Proprietaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: "proprietaire", cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: "proprietaire", targetEntity: Offre::class, cascade: ["persist", "remove"])]
    private Collection $offres;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => 0])]
    private ?float $revenuTotal = 0.0;

    public function getRevenuTotal(): ?float
    {
        return $this->revenuTotal;
    }

    public function setRevenuTotal(float $revenuTotal): static
    {
        $this->revenuTotal = $revenuTotal;
        return $this;
    }

    public function calculerTotalRevenu(): float
    {
        $total = 0;
        foreach ($this->offres as $offre) {
            $total += $offre->getRevenuProprietaire();
        }
        $this->setRevenuTotal($total);
        return $total;
    }

    public function __construct()
    {
        $this->offres = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(Offre $offre): static
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setProprietaire($this);
        }

        return $this;
    }

    public function removeOffre(Offre $offre): static
    {
        if ($this->offres->removeElement($offre)) {
            if ($offre->getProprietaire() === $this) {
                $offre->setProprietaire(null);
            }
        }

        return $this;
    }
}
