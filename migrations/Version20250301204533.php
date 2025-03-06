<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301204533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emprunteur ADD nom VARCHAR(255) NOT NULL, ADD numero_permis VARCHAR(50) NOT NULL, ADD date_expiration DATE NOT NULL');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F76C50E4A');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES proprietaire (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emprunteur DROP nom, DROP numero_permis, DROP date_expiration');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F76C50E4A');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES proprietaire (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
