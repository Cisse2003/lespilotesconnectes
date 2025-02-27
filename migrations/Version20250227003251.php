<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227003251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F76C50E4A');
        $this->addSql('ALTER TABLE offre ADD date_debut_disponibilite DATE DEFAULT NULL, ADD date_fin_disponibilite DATE DEFAULT NULL, ADD lieu_garage VARCHAR(100) NOT NULL, ADD prix NUMERIC(10, 2) NOT NULL, ADD disponibilite TINYINT(1) NOT NULL, ADD description LONGTEXT DEFAULT NULL, CHANGE date_creation date_creation DATE NOT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES proprietaire (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F76C50E4A');
        $this->addSql('ALTER TABLE offre DROP date_debut_disponibilite, DROP date_fin_disponibilite, DROP lieu_garage, DROP prix, DROP disponibilite, DROP description, CHANGE date_creation date_creation DATETIME NOT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
