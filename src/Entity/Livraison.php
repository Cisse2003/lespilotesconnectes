<?php

namespace App\Entity;

use App\Repository\LivraisonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
class Livraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?float $tarifs = null;


    #[ORM\Column]
    private ?bool $disponibilite = null;

    #[ORM\OneToOne(inversedBy: 'livraison', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offre $offre = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTarifs(?float $tarifs): self
    {
        $this->tarifs = $tarifs;
        return $this;
    }

    public function getTarifs(): ?float
    {
        return $this->tarifs;
    }

    public function setDisponibilite(?bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setOffre(?Offre $offre): self
    {
        $this->offre = $offre;
        return $this;
    }

    public function getOffre(): ?Offre
    {
        return $this->offre;
    }
}
