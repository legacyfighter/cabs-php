<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220414204404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE transit_details_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE transit_details (id INT NOT NULL, client_id INT DEFAULT NULL, from_id INT DEFAULT NULL, to_id INT DEFAULT NULL, transit_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, car_type VARCHAR(255) DEFAULT NULL, distance DOUBLE PRECISION NOT NULL, started TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, price INT DEFAULT NULL, estimated_price INT DEFAULT NULL, drivers_fee INT DEFAULT NULL, driver_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, version INT DEFAULT 1 NOT NULL, tariff_km_rate DOUBLE PRECISION NOT NULL, tariff_name VARCHAR(255) NOT NULL, tariff_base_fee INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C6C5CB0219EB6921 ON transit_details (client_id)');
        $this->addSql('CREATE INDEX IDX_C6C5CB0278CED90B ON transit_details (from_id)');
        $this->addSql('CREATE INDEX IDX_C6C5CB0230354A65 ON transit_details (to_id)');
        $this->addSql('COMMENT ON COLUMN transit_details.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.completed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.distance IS \'(DC2Type:distance)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.started IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.accepted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.published_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.price IS \'(DC2Type:money)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.estimated_price IS \'(DC2Type:money)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.drivers_fee IS \'(DC2Type:money)\'');
        $this->addSql('ALTER TABLE transit_details ADD CONSTRAINT FK_C6C5CB0219EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_details ADD CONSTRAINT FK_C6C5CB0278CED90B FOREIGN KEY (from_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_details ADD CONSTRAINT FK_C6C5CB0230354A65 FOREIGN KEY (to_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit DROP CONSTRAINT fk_9eaea8378ced90b');
        $this->addSql('ALTER TABLE transit DROP CONSTRAINT fk_9eaea8330354a65');
        $this->addSql('DROP INDEX idx_9eaea8378ced90b');
        $this->addSql('DROP INDEX idx_9eaea8330354a65');
        $this->addSql('ALTER TABLE transit DROP from_id');
        $this->addSql('ALTER TABLE transit DROP to_id');
        $this->addSql('ALTER TABLE transit DROP accepted_at');
        $this->addSql('ALTER TABLE transit DROP started');
        $this->addSql('ALTER TABLE transit DROP complete_at');
        $this->addSql('ALTER TABLE transit DROP date_time');
        $this->addSql('ALTER TABLE transit DROP car_type');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE transit_details_id_seq CASCADE');
        $this->addSql('DROP TABLE transit_details');
        $this->addSql('ALTER TABLE transit ADD from_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD started TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD complete_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD date_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD car_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN transit.accepted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.started IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.complete_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE transit ADD CONSTRAINT fk_9eaea8378ced90b FOREIGN KEY (from_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit ADD CONSTRAINT fk_9eaea8330354a65 FOREIGN KEY (to_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9eaea8378ced90b ON transit (from_id)');
        $this->addSql('CREATE INDEX idx_9eaea8330354a65 ON transit (to_id)');
    }
}
