<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220419070117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE transit_driver_rejected');
        $this->addSql('DROP TABLE transit_driver_proposed');
        $this->addSql('ALTER TABLE driver_position DROP CONSTRAINT fk_b5b7ac9c3423909');
        $this->addSql('DROP INDEX idx_b5b7ac9c3423909');
        $this->addSql('ALTER TABLE driver_position ALTER driver_id SET NOT NULL');
        $this->addSql('ALTER TABLE transit DROP CONSTRAINT fk_9eaea83c3423909');
        $this->addSql('DROP INDEX idx_9eaea83c3423909');
        $this->addSql('ALTER TABLE transit ADD drivers_rejections JSON NOT NULL');
        $this->addSql('ALTER TABLE transit ADD proposed_drivers JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transit_driver_rejected (transit_id INT NOT NULL, driver_id INT NOT NULL, PRIMARY KEY(transit_id, driver_id))');
        $this->addSql('CREATE INDEX idx_f6790425c3423909 ON transit_driver_rejected (driver_id)');
        $this->addSql('CREATE INDEX idx_f6790425617c70e0 ON transit_driver_rejected (transit_id)');
        $this->addSql('CREATE TABLE transit_driver_proposed (transit_id INT NOT NULL, driver_id INT NOT NULL, PRIMARY KEY(transit_id, driver_id))');
        $this->addSql('CREATE INDEX idx_a1718164c3423909 ON transit_driver_proposed (driver_id)');
        $this->addSql('CREATE INDEX idx_a1718164617c70e0 ON transit_driver_proposed (transit_id)');
        $this->addSql('ALTER TABLE transit_driver_rejected ADD CONSTRAINT fk_f6790425617c70e0 FOREIGN KEY (transit_id) REFERENCES transit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_driver_rejected ADD CONSTRAINT fk_f6790425c3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_driver_proposed ADD CONSTRAINT fk_a1718164617c70e0 FOREIGN KEY (transit_id) REFERENCES transit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_driver_proposed ADD CONSTRAINT fk_a1718164c3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit DROP drivers_rejections');
        $this->addSql('ALTER TABLE transit DROP proposed_drivers');
        $this->addSql('ALTER TABLE transit ADD CONSTRAINT fk_9eaea83c3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9eaea83c3423909 ON transit (driver_id)');
        $this->addSql('ALTER TABLE driver_position ALTER driver_id DROP NOT NULL');
        $this->addSql('ALTER TABLE driver_position ADD CONSTRAINT fk_b5b7ac9c3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_b5b7ac9c3423909 ON driver_position (driver_id)');
    }
}
