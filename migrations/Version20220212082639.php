<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220212082639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awarded_miles ADD account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE awarded_miles ADD CONSTRAINT FK_43471B899B6B5FBA FOREIGN KEY (account_id) REFERENCES awards_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_43471B899B6B5FBA ON awarded_miles (account_id)');
        $this->addSql('COMMENT ON COLUMN awarded_miles.miles IS \'(DC2Type:miles)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awarded_miles DROP CONSTRAINT FK_43471B899B6B5FBA');
        $this->addSql('DROP INDEX IDX_43471B899B6B5FBA');
        $this->addSql('ALTER TABLE awarded_miles DROP account_id');
        $this->addSql('COMMENT ON COLUMN awarded_miles.miles IS NULL');
    }
}
