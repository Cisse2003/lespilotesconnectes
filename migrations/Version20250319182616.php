<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319182616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emprunteur DROP INDEX IDX_952067DEFB88E14F, ADD UNIQUE INDEX UNIQ_952067DEFB88E14F (utilisateur_id)');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBF0840037');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBF0840037 FOREIGN KEY (emprunteur_id) REFERENCES emprunteur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBF0840037');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBF0840037 FOREIGN KEY (emprunteur_id) REFERENCES emprunteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE emprunteur DROP INDEX UNIQ_952067DEFB88E14F, ADD INDEX IDX_952067DEFB88E14F (utilisateur_id)');
    }
}
