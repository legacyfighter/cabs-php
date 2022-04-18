<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220425120006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awarded_miles DROP CONSTRAINT fk_43471b89617c70e0');
        $this->addSql('DROP INDEX idx_43471b89617c70e0');
        $this->addSql('ALTER TABLE claim DROP CONSTRAINT fk_a769de27617c70e0');
        $this->addSql('DROP INDEX idx_a769de27617c70e0');
        $this->addSql('ALTER TABLE claim ADD transit_price INT DEFAULT NULL');
        $this->addSql('ALTER TABLE claim ALTER transit_id SET NOT NULL');
        $this->addSql('COMMENT ON COLUMN claim.transit_price IS \'(DC2Type:money)\'');
        $this->addSql('ALTER TABLE transit DROP drivers_fee');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awarded_miles ADD CONSTRAINT fk_43471b89617c70e0 FOREIGN KEY (transit_id) REFERENCES transit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_43471b89617c70e0 ON awarded_miles (transit_id)');
        $this->addSql('ALTER TABLE claim DROP transit_price');
        $this->addSql('ALTER TABLE claim ALTER transit_id DROP NOT NULL');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT fk_a769de27617c70e0 FOREIGN KEY (transit_id) REFERENCES transit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a769de27617c70e0 ON claim (transit_id)');
        $this->addSql('ALTER TABLE transit ADD drivers_fee INT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN transit.drivers_fee IS \'(DC2Type:money)\'');
    }
}
