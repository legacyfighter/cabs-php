<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220121160323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE awarded_miles_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE awards_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE car_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE claim_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE claim_attachment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE contract_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE contract_attachment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE driver_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE driver_attribute_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE driver_fee_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE driver_position_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE driver_session_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE address (id INT NOT NULL, country VARCHAR(255) NOT NULL, district VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, building_number INT NOT NULL, additional_number INT NULL, postal_code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, hash BIGINT NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4E6F81D1B862B8 ON address (hash)');
        $this->addSql('CREATE TABLE awarded_miles (id INT NOT NULL, client_id INT DEFAULT NULL, transit_id INT DEFAULT NULL, miles INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expiration_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_special BOOLEAN NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_43471B8919EB6921 ON awarded_miles (client_id)');
        $this->addSql('CREATE INDEX IDX_43471B89617C70E0 ON awarded_miles (transit_id)');
        $this->addSql('COMMENT ON COLUMN awarded_miles.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN awarded_miles.expiration_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE awards_account (id INT NOT NULL, client_id INT DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_active BOOLEAN NOT NULL, transactions INT NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F2995BB19EB6921 ON awards_account (client_id)');
        $this->addSql('COMMENT ON COLUMN awards_account.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE car_type (id INT NOT NULL, car_class VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, cars_counter INT NOT NULL, min_no_of_cars_to_activate_class INT NOT NULL, active_cars_counter INT NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE claim (id INT NOT NULL, owner_id INT DEFAULT NULL, transit_id INT DEFAULT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, completion_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, change_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, reason VARCHAR(255) NOT NULL, incident_description VARCHAR(255) DEFAULT NULL, completion_mode VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, claim_no VARCHAR(255) NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A769DE277E3C61F9 ON claim (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A769DE27617C70E0 ON claim (transit_id)');
        $this->addSql('COMMENT ON COLUMN claim.creation_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN claim.completion_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN claim.change_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE claim_attachment (id INT NOT NULL, claim_id INT DEFAULT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, description VARCHAR(255) NOT NULL, data TEXT NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A423D5077096A49F ON claim_attachment (claim_id)');
        $this->addSql('COMMENT ON COLUMN claim_attachment.creation_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE client (id INT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, default_payment_type VARCHAR(255) NOT NULL, client_type VARCHAR(255) NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE contract (id INT NOT NULL, partner_name VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rejected_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, change_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status VARCHAR(255) NOT NULL, contract_no VARCHAR(255) NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN contract.creation_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN contract.accepted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN contract.rejected_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN contract.change_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE contract_attachment (id INT NOT NULL, contract_id INT DEFAULT NULL, data TEXT NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rejected_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, change_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status VARCHAR(255) NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_601E78922576E0FD ON contract_attachment (contract_id)');
        $this->addSql('COMMENT ON COLUMN contract_attachment.creation_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN contract_attachment.accepted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN contract_attachment.rejected_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN contract_attachment.change_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE driver (id INT NOT NULL, fee_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, photo TEXT DEFAULT NULL, driver_license VARCHAR(255) NOT NULL, is_occupied BOOLEAN NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_11667CD9AB45AECA ON driver (fee_id)');
        $this->addSql('CREATE TABLE driver_attribute (id INT NOT NULL, driver_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D558E4E2C3423909 ON driver_attribute (driver_id)');
        $this->addSql('CREATE TABLE driver_fee (id INT NOT NULL, driver_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, amount INT NOT NULL, min INT DEFAULT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8482F6BBC3423909 ON driver_fee (driver_id)');
        $this->addSql('CREATE TABLE driver_position (id INT NOT NULL, driver_id INT DEFAULT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, seen_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B5B7AC9C3423909 ON driver_position (driver_id)');
        $this->addSql('COMMENT ON COLUMN driver_position.seen_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE driver_session (id INT NOT NULL, driver_id INT DEFAULT NULL, logged_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, logged_out_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, plates_number VARCHAR(255) NOT NULL, car_class VARCHAR(255) NOT NULL, car_brand VARCHAR(255) NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_15BBB1B4C3423909 ON driver_session (driver_id)');
        $this->addSql('COMMENT ON COLUMN driver_session.logged_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN driver_session.logged_out_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE invoice (id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, subject_name VARCHAR(255) NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE transit (id INT NOT NULL, from_id INT DEFAULT NULL, to_id INT DEFAULT NULL, driver_id INT DEFAULT NULL, client_id INT DEFAULT NULL, driver_payment_status VARCHAR(255) DEFAULT NULL, client_payment_status VARCHAR(255) DEFAULT NULL, payment_type VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, pickup_address_change_counter INT NOT NULL, accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, started TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, complete_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, awaiting_drivers_responses INT NOT NULL, factor INT DEFAULT NULL, km DOUBLE PRECISION DEFAULT NULL, price INT DEFAULT NULL, estimated_price INT DEFAULT NULL, drivers_fee INT DEFAULT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, published TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, car_type VARCHAR(255) DEFAULT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9EAEA8378CED90B ON transit (from_id)');
        $this->addSql('CREATE INDEX IDX_9EAEA8330354A65 ON transit (to_id)');
        $this->addSql('CREATE INDEX IDX_9EAEA83C3423909 ON transit (driver_id)');
        $this->addSql('CREATE INDEX IDX_9EAEA8319EB6921 ON transit (client_id)');
        $this->addSql('COMMENT ON COLUMN transit.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.accepted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.started IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.complete_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN transit.published IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE transit_driver_rejected (transit_id INT NOT NULL, driver_id INT NOT NULL, PRIMARY KEY(transit_id, driver_id))');
        $this->addSql('CREATE INDEX IDX_F6790425617C70E0 ON transit_driver_rejected (transit_id)');
        $this->addSql('CREATE INDEX IDX_F6790425C3423909 ON transit_driver_rejected (driver_id)');
        $this->addSql('CREATE TABLE transit_driver_proposed (transit_id INT NOT NULL, driver_id INT NOT NULL, PRIMARY KEY(transit_id, driver_id))');
        $this->addSql('CREATE INDEX IDX_A1718164617C70E0 ON transit_driver_proposed (transit_id)');
        $this->addSql('CREATE INDEX IDX_A1718164C3423909 ON transit_driver_proposed (driver_id)');
        $this->addSql('ALTER TABLE awarded_miles ADD CONSTRAINT FK_43471B8919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE awarded_miles ADD CONSTRAINT FK_43471B89617C70E0 FOREIGN KEY (transit_id) REFERENCES transit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE awards_account ADD CONSTRAINT FK_6F2995BB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT FK_A769DE277E3C61F9 FOREIGN KEY (owner_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE claim ADD CONSTRAINT FK_A769DE27617C70E0 FOREIGN KEY (transit_id) REFERENCES transit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE claim_attachment ADD CONSTRAINT FK_A423D5077096A49F FOREIGN KEY (claim_id) REFERENCES claim (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contract_attachment ADD CONSTRAINT FK_601E78922576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driver ADD CONSTRAINT FK_11667CD9AB45AECA FOREIGN KEY (fee_id) REFERENCES driver_fee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driver_attribute ADD CONSTRAINT FK_D558E4E2C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driver_fee ADD CONSTRAINT FK_8482F6BBC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driver_position ADD CONSTRAINT FK_B5B7AC9C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driver_session ADD CONSTRAINT FK_15BBB1B4C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit ADD CONSTRAINT FK_9EAEA8378CED90B FOREIGN KEY (from_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit ADD CONSTRAINT FK_9EAEA8330354A65 FOREIGN KEY (to_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit ADD CONSTRAINT FK_9EAEA83C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit ADD CONSTRAINT FK_9EAEA8319EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_driver_rejected ADD CONSTRAINT FK_F6790425617C70E0 FOREIGN KEY (transit_id) REFERENCES transit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_driver_rejected ADD CONSTRAINT FK_F6790425C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_driver_proposed ADD CONSTRAINT FK_A1718164617C70E0 FOREIGN KEY (transit_id) REFERENCES transit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transit_driver_proposed ADD CONSTRAINT FK_A1718164C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transit DROP CONSTRAINT FK_9EAEA8378CED90B');
        $this->addSql('ALTER TABLE transit DROP CONSTRAINT FK_9EAEA8330354A65');
        $this->addSql('ALTER TABLE claim_attachment DROP CONSTRAINT FK_A423D5077096A49F');
        $this->addSql('ALTER TABLE awarded_miles DROP CONSTRAINT FK_43471B8919EB6921');
        $this->addSql('ALTER TABLE awards_account DROP CONSTRAINT FK_6F2995BB19EB6921');
        $this->addSql('ALTER TABLE claim DROP CONSTRAINT FK_A769DE277E3C61F9');
        $this->addSql('ALTER TABLE transit DROP CONSTRAINT FK_9EAEA8319EB6921');
        $this->addSql('ALTER TABLE contract_attachment DROP CONSTRAINT FK_601E78922576E0FD');
        $this->addSql('ALTER TABLE driver_attribute DROP CONSTRAINT FK_D558E4E2C3423909');
        $this->addSql('ALTER TABLE driver_fee DROP CONSTRAINT FK_8482F6BBC3423909');
        $this->addSql('ALTER TABLE driver_position DROP CONSTRAINT FK_B5B7AC9C3423909');
        $this->addSql('ALTER TABLE driver_session DROP CONSTRAINT FK_15BBB1B4C3423909');
        $this->addSql('ALTER TABLE transit DROP CONSTRAINT FK_9EAEA83C3423909');
        $this->addSql('ALTER TABLE transit_driver_rejected DROP CONSTRAINT FK_F6790425C3423909');
        $this->addSql('ALTER TABLE transit_driver_proposed DROP CONSTRAINT FK_A1718164C3423909');
        $this->addSql('ALTER TABLE driver DROP CONSTRAINT FK_11667CD9AB45AECA');
        $this->addSql('ALTER TABLE awarded_miles DROP CONSTRAINT FK_43471B89617C70E0');
        $this->addSql('ALTER TABLE claim DROP CONSTRAINT FK_A769DE27617C70E0');
        $this->addSql('ALTER TABLE transit_driver_rejected DROP CONSTRAINT FK_F6790425617C70E0');
        $this->addSql('ALTER TABLE transit_driver_proposed DROP CONSTRAINT FK_A1718164617C70E0');
        $this->addSql('DROP SEQUENCE address_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE awarded_miles_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE awards_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE car_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE claim_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE claim_attachment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE client_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE contract_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE contract_attachment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE driver_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE driver_attribute_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE driver_fee_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE driver_position_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE driver_session_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transit_id_seq CASCADE');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE awarded_miles');
        $this->addSql('DROP TABLE awards_account');
        $this->addSql('DROP TABLE car_type');
        $this->addSql('DROP TABLE claim');
        $this->addSql('DROP TABLE claim_attachment');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE contract_attachment');
        $this->addSql('DROP TABLE driver');
        $this->addSql('DROP TABLE driver_attribute');
        $this->addSql('DROP TABLE driver_fee');
        $this->addSql('DROP TABLE driver_position');
        $this->addSql('DROP TABLE driver_session');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE transit');
        $this->addSql('DROP TABLE transit_driver_rejected');
        $this->addSql('DROP TABLE transit_driver_proposed');
    }
}
