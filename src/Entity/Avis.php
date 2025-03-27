<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "avis")]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    #[Assert\Range(min: 1, max: 5)] // Notation entre 1 et 5
    private int $note;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $auteur = null;

    // Relation avec l'entité notée (par exemple, Offre)
    #[ORM\ManyToOne(targetEntity: Offre::class, inversedBy: "avis")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offre $offre = null;

    #[ORM\Column(type: "datetime")]
    private \DateTime $dateCreation;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    // Getters et Setters
    public function getId(): ?int { return $this->id; }

    public function getNote(): int { return $this->note; }
    public function setNote(int $note): self { $this->note = $note; return $this; }

    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): self { $this->commentaire = $commentaire; return $this; }

    public function getAuteur(): ?Utilisateur { return $this->auteur; }
    public function setAuteur(?Utilisateur $auteur): self { $this->auteur = $auteur; return $this; }

    public function getOffre(): ?Offre { return $this->offre; }
    public function setOffre(?Offre $offre): self { $this->offre = $offre; return $this; }

    public function getDateCreation(): \DateTime { return $this->dateCreation; }
}