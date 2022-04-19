<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220419063121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE driver_session DROP CONSTRAINT fk_15bbb1b4c3423909');
        $this->addSql('DROP INDEX idx_15bbb1b4c3423909');
        $this->addSql('ALTER TABLE driver_session ALTER driver_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE driver_session ALTER driver_id DROP NOT NULL');
        $this->addSql('ALTER TABLE driver_session ADD CONSTRAINT fk_15bbb1b4c3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_15bbb1b4c3423909 ON driver_session (driver_id)');
    }
}
