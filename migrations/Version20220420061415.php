<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220420061415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE driver_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE request_for_transit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transit_demand_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE driver_assignment (id INT NOT NULL, request_id UUID NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, assigned_driver INT DEFAULT NULL, drivers_rejections JSON NOT NULL, proposed_drivers JSON NOT NULL, awaiting_drivers_responses INT NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN driver_assignment.request_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN driver_assignment.published_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE request_for_transit (id INT NOT NULL, request_uuid UUID NOT NULL, distance DOUBLE PRECISION NOT NULL, version INT DEFAULT 1 NOT NULL, tariff_km_rate DOUBLE PRECISION NOT NULL, tariff_name VARCHAR(255) NOT NULL, tariff_base_fee INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN request_for_transit.request_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN request_for_transit.distance IS \'(DC2Type:distance)\'');
        $this->addSql('COMMENT ON COLUMN request_for_transit.tariff_base_fee IS \'(DC2Type:money)\'');
        $this->addSql('CREATE TABLE transit_demand (id INT NOT NULL, request_uuid UUID NOT NULL, status VARCHAR(255) NOT NULL, pickup_address_change_counter INT NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN transit_demand.request_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE transit DROP CONSTRAINT fk_9eaea8319eb6921');
        $this->addSql('DROP INDEX idx_9eaea8319eb6921');
        $this->addSql('ALTER TABLE transit ADD request_uuid UUID NOT NULL');
        $this->addSql('ALTER TABLE transit DROP client_id');
        $this->addSql('ALTER TABLE transit DROP driver_id');
        $this->addSql('ALTER TABLE transit DROP driver_payment_status');
        $this->addSql('ALTER TABLE transit DROP client_payment_status');
        $this->addSql('ALTER TABLE transit DROP payment_type');
        $this->addSql('ALTER TABLE transit DROP date');
        $this->addSql('ALTER TABLE transit DROP pickup_address_change_counter');
        $this->addSql('ALTER TABLE transit DROP awaiting_drivers_responses');
        $this->addSql('ALTER TABLE transit DROP price');
        $this->addSql('ALTER TABLE transit DROP estimated_price');
        $this->addSql('ALTER TABLE transit DROP published');
        $this->addSql('ALTER TABLE transit DROP drivers_rejections');
        $this->addSql('ALTER TABLE transit DROP proposed_drivers');
        $this->addSql('ALTER TABLE transit ALTER tariff_base_fee TYPE INT');
        $this->addSql('ALTER TABLE transit ALTER tariff_base_fee DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN transit.request_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN transit.tariff_base_fee IS \'(DC2Type:money)\'');
        $this->addSql('ALTER TABLE transit_details ADD request_uuid UUID NOT NULL');
        $this->addSql('ALTER TABLE transit_details ALTER transit_id DROP NOT NULL');
        $this->addSql('ALTER TABLE transit_details ALTER tariff_base_fee TYPE INT');
        $this->addSql('ALTER TABLE transit_details ALTER tariff_base_fee DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN transit_details.request_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN transit_details.tariff_base_fee IS \'(DC2Type:money)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE driver_assignment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE request_for_transit_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transit_demand_id_seq CASCADE');
        $this->addSql('DROP TABLE driver_assignment');
        $this->addSql('DROP TABLE request_for_transit');
        $this->addSql('DROP TABLE transit_demand');
        $this->addSql('ALTER TABLE transit ADD client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD driver_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD driver_payment_status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD client_payment_status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD payment_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD pickup_address_change_counter INT NOT NULL');
        $this->addSql('ALTER TABLE transit ADD awaiting_drivers_responses INT NOT NULL');
        $this->addSql('ALTER TABLE transit ADD price INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD estimated_price INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD published TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE transit ADD drivers_rejections JSON NOT NULL');
        $this->addSql('ALTER TABLE transit ADD proposed_drivers JSON NOT NULL');
        $this->addSql('ALTER TABLE transit DROP request_uuid');
        $this->addSql('ALTER TABLE transit ALTER tariff_base_fee TYPE INT');
        $this->addSql('ALTER TABLE transit ALTER tariff_base_fee DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN transit.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.price IS \'(DC2Type:money)\'');
        $this->addSql('COMMENT ON COLUMN transit.estimated_price IS \'(DC2Type:money)\'');
        $this->addSql('COMMENT ON COLUMN transit.published IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.tariff_base_fee IS NULL');
        $this->addSql('ALTER TABLE transit ADD CONSTRAINT fk_9eaea8319eb6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9eaea8319eb6921 ON transit (client_id)');
        $this->addSql('ALTER TABLE transit_details DROP request_uuid');
        $this->addSql('ALTER TABLE transit_details ALTER transit_id SET NOT NULL');
        $this->addSql('ALTER TABLE transit_details ALTER tariff_base_fee TYPE INT');
        $this->addSql('ALTER TABLE transit_details ALTER tariff_base_fee DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN transit_details.tariff_base_fee IS NULL');
    }
}
