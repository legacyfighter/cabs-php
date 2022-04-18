<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220418223109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awarded_miles DROP CONSTRAINT fk_43471b8919eb6921');
        $this->addSql('DROP INDEX idx_43471b8919eb6921');
        $this->addSql('ALTER TABLE awarded_miles ALTER client_id SET NOT NULL');
        $this->addSql('ALTER TABLE awards_account DROP CONSTRAINT fk_6f2995bb19eb6921');
        $this->addSql('DROP INDEX uniq_6f2995bb19eb6921');
        $this->addSql('ALTER TABLE awards_account ALTER client_id SET NOT NULL');
        $this->addSql('ALTER TABLE claim DROP CONSTRAINT fk_a769de277e3c61f9');
        $this->addSql('DROP INDEX idx_a769de277e3c61f9');
        $this->addSql('ALTER TABLE claim ALTER owner_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awarded_miles ALTER client_id DROP NOT NULL');
        $this->addSql('ALTER TABLE awarded_miles ADD CONSTRAINT fk_43471b8919eb6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_43471b8919eb6921 ON awarded_miles (client_id)');
        $this->addSql('ALTER TABLE claim ALTER owner_id DROP NOT NULL');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT fk_a769de277e3c61f9 FOREIGN KEY (owner_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a769de277e3c61f9 ON claim (owner_id)');
        $this->addSql('ALTER TABLE awards_account ALTER client_id DROP NOT NULL');
        $this->addSql('ALTER TABLE awards_account ADD CONSTRAINT fk_6f2995bb19eb6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_6f2995bb19eb6921 ON awards_account (client_id)');
    }
}
