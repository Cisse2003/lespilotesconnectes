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
    public function setMarque(string $marque): self
{
    $this->marque = $marque;
    return $this;
}

public function getMarque(): ?string
{
    return $this->marque;
}

public function setModele(string $modele): self
{
    $this->modele = $modele;
    return $this;
}

public function getModele(): ?string
{
    return $this->modele;
}

public function setImmatriculation(string $immatriculation): self
{
    $this->immatriculation = $immatriculation;
    return $this;
}

public function getImmatriculation(): ?string
{
    return $this->immatriculation;
}

public function setAnnee(int $annee): self
{
    $this->annee = $annee;
    return $this;
}

public function getAnnee(): ?int
{
    return $this->annee;
}

public function setNombrePlaces(int $nombrePlaces): self
{
    $this->nombrePlaces = $nombrePlaces;
    return $this;
}

public function getNombrePlaces(): ?int
{
    return $this->nombrePlaces;
}

public function setVolumeCoffre(int $volumeCoffre): self
{
    $this->volumeCoffre = $volumeCoffre;
    return $this;
}

public function getVolumeCoffre(): ?int
{
    return $this->volumeCoffre;
}

public function setTypeEssence(string $typeEssence): self
{
    $this->typeEssence = $typeEssence;
    return $this;
}

public function getTypeEssence(): ?string
{
    return $this->typeEssence;
}

}

