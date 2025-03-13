<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311212758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BBFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE administrateur ADD CONSTRAINT FK_32EB52E8FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE offre ADD photos JSON DEFAULT NULL, DROP date_disponibilite');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offre ADD date_disponibilite DATE DEFAULT NULL, DROP photos');
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BBFB88E14F');
        $this->addSql('ALTER TABLE administrateur DROP FOREIGN KEY FK_32EB52E8FB88E14F');
    }
}
