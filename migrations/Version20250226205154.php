<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226205154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnement (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, type VARCHAR(50) NOT NULL, prix NUMERIC(10, 2) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, INDEX IDX_351268BBFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE administrateur (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, role VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_32EB52E8FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emprunteur (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, UNIQUE INDEX UNIQ_952067DEFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE litige (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, date_signalement DATE NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_EEE9D46D64D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, offre_id INT NOT NULL, adresse VARCHAR(255) NOT NULL, tarifs NUMERIC(10, 2) NOT NULL, disponibilite TINYINT(1) NOT NULL, INDEX IDX_A60C9F1F4CC8505A (offre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, offre_id INT NOT NULL, emprunteur_id INT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, INDEX IDX_5E9E89CB4CC8505A (offre_id), INDEX IDX_5E9E89CBF0840037 (emprunteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offre (id INT AUTO_INCREMENT NOT NULL, date_creation DATE NOT NULL, lieu_garage VARCHAR(100) NOT NULL, prix NUMERIC(10, 2) NOT NULL, disponibilite TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, telephone VARCHAR(15) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, profile_image VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voiture (id INT AUTO_INCREMENT NOT NULL, marque VARCHAR(50) NOT NULL, modele VARCHAR(50) NOT NULL, immatriculation VARCHAR(20) NOT NULL, annee INT NOT NULL, nombre_places INT NOT NULL, volume_coffre INT NOT NULL, type_essence VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BBFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE administrateur ADD CONSTRAINT FK_32EB52E8FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE emprunteur ADD CONSTRAINT FK_952067DEFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE litige ADD CONSTRAINT FK_EEE9D46D64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1F4CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB4CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBF0840037 FOREIGN KEY (emprunteur_id) REFERENCES emprunteur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BBFB88E14F');
        $this->addSql('ALTER TABLE administrateur DROP FOREIGN KEY FK_32EB52E8FB88E14F');
        $this->addSql('ALTER TABLE emprunteur DROP FOREIGN KEY FK_952067DEFB88E14F');
        $this->addSql('ALTER TABLE litige DROP FOREIGN KEY FK_EEE9D46D64D218E');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1F4CC8505A');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB4CC8505A');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBF0840037');
        $this->addSql('DROP TABLE abonnement');
        $this->addSql('DROP TABLE administrateur');
        $this->addSql('DROP TABLE emprunteur');
        $this->addSql('DROP TABLE litige');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE offre');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE voiture');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
