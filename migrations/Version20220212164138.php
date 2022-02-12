<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220212164138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE contract_attachment_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE contract_attachment_data (id INT NOT NULL, contract_attachment_no UUID NOT NULL, data TEXT NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN contract_attachment_data.contract_attachment_no IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN contract_attachment_data.creation_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE contract_attachment ADD contract_attachment_no UUID NOT NULL');
        $this->addSql('ALTER TABLE contract_attachment DROP data');
        $this->addSql('ALTER TABLE contract_attachment DROP creation_date');
        $this->addSql('COMMENT ON COLUMN contract_attachment.contract_attachment_no IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE contract_attachment_data_id_seq CASCADE');
        $this->addSql('DROP TABLE contract_attachment_data');
        $this->addSql('ALTER TABLE contract_attachment ADD data TEXT NOT NULL');
        $this->addSql('ALTER TABLE contract_attachment ADD creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE contract_attachment DROP contract_attachment_no');
        $this->addSql('COMMENT ON COLUMN contract_attachment.creation_date IS \'(DC2Type:datetime_immutable)\'');
    }
}
