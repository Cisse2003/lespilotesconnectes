<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LitigeRepository")
 */
class Litige
{
    // Identifiant unique
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // Référence à la réservation concernée
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Reservation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reservation;

    // Propriétaire concerné par le litige
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Utilisateur")
     * @ORM\JoinColumn(nullable=false)
     */
    private $proprietaire;

    // Emprunteur concerné par le litige
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Utilisateur")
     * @ORM\JoinColumn(nullable=false)
     */
    private $emprunteur;

    // Description du litige
    /**
     * @ORM\Column(type="text")
     */
    private $description;

    // Statut du litige (en cours, résolu, fermé)
    /**
     * @ORM\Column(type="string", length=20)
     */
    private $statut;

    // Date du signalement du litige
    /**
     * @ORM\Column(type="datetime")
     */
    private $dateSignalement;

    public function __construct()
    {
        $this->dateSignalement = new \DateTime();
        $this->statut = 'en cours';
    }

    // Getters et setters pour chaque propriété...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;

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
}

