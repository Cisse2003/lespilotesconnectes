<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224123727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offre ADD voiture_id INT NOT NULL, ADD proprietaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id)');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES proprietaire (id)');
        $this->addSql('CREATE INDEX IDX_AF86866F181A8BA ON offre (voiture_id)');
        $this->addSql('CREATE INDEX IDX_AF86866F76C50E4A ON offre (proprietaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F181A8BA');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F76C50E4A');
        $this->addSql('DROP INDEX IDX_AF86866F181A8BA ON offre');
        $this->addSql('DROP INDEX IDX_AF86866F76C50E4A ON offre');
        $this->addSql('ALTER TABLE offre DROP voiture_id, DROP proprietaire_id');
    }
}
