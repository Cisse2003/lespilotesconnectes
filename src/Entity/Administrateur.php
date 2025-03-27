<?php

namespace App\Entity;

use App\Repository\AdministrateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;



#[ORM\Entity(repositoryClass: AdministrateurRepository::class)]
class Administrateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\OneToOne(inversedBy: "administrateur", cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: true)] // Permettre un Administrateur sans Utilisateur
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => 0])]
    private ?float $commissionTotale = 0;

    public function getCommissionTotale(): ?float
    {
        return $this->commissionTotale;
    }

    public function setCommissionTotale(float $commission): self
    {
        $this->commissionTotale = $commission;
        return $this;
    }

    public function ajouterCommission(float $montant): self
    {
        $this->commissionTotale += $montant;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        // S'assurer que chaque administrateur a toujours ROLE_ADMIN
        return ['ROLE_ADMIN'];
    }


    public function eraseCredentials(): void
    {
        // Si l'entité stocke des informations sensibles, les nettoyer ici.
    }

}
