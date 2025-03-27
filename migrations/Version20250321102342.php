<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321102342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE administrateur ADD password VARCHAR(255) NOT NULL, CHANGE utilisateur_id utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE emprunteur ADD nom VARCHAR(255) NOT NULL, ADD numero_permis VARCHAR(50) DEFAULT NULL, ADD date_expiration DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE livraison DROP INDEX IDX_A60C9F1F4CC8505A, ADD UNIQUE INDEX UNIQ_A60C9F1F4CC8505A (offre_id)');
        $this->addSql('ALTER TABLE livraison DROP adresse, CHANGE tarifs tarifs NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBF0840037');
        $this->addSql('ALTER TABLE location ADD commission NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBF0840037 FOREIGN KEY (emprunteur_id) REFERENCES emprunteur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offre ADD photos JSON DEFAULT NULL, ADD suspended_until DATETIME DEFAULT NULL, DROP date_disponibilite');
        $this->addSql('ALTER TABLE utilisateur ADD roles JSON NOT NULL, ADD is_verified TINYINT(1) NOT NULL, ADD confirmation_token VARCHAR(255) DEFAULT NULL, ADD suspended_until DATETIME DEFAULT NULL, CHANGE telephone telephone VARCHAR(15) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3C05FB297 ON utilisateur (confirmation_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_1D1C63B3C05FB297 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP roles, DROP is_verified, DROP confirmation_token, DROP suspended_until, CHANGE telephone telephone VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBF0840037');
        $this->addSql('ALTER TABLE location DROP commission');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBF0840037 FOREIGN KEY (emprunteur_id) REFERENCES emprunteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE administrateur DROP password, CHANGE utilisateur_id utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE emprunteur DROP nom, DROP numero_permis, DROP date_expiration');
        $this->addSql('ALTER TABLE offre ADD date_disponibilite DATE DEFAULT NULL, DROP photos, DROP suspended_until');
        $this->addSql('ALTER TABLE livraison DROP INDEX UNIQ_A60C9F1F4CC8505A, ADD INDEX IDX_A60C9F1F4CC8505A (offre_id)');
        $this->addSql('ALTER TABLE livraison ADD adresse VARCHAR(255) NOT NULL, CHANGE tarifs tarifs NUMERIC(10, 2) NOT NULL');
    }
}
