<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220326082730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE party (id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN party.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE party_relationship (id UUID NOT NULL, party_a_id UUID DEFAULT NULL, party_b_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, role_a VARCHAR(255) NOT NULL, role_b VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BFC4C273247D928B ON party_relationship (party_a_id)');
        $this->addSql('CREATE INDEX IDX_BFC4C27336C83D65 ON party_relationship (party_b_id)');
        $this->addSql('COMMENT ON COLUMN party_relationship.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN party_relationship.party_a_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN party_relationship.party_b_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE party_role (id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN party_role.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE party_relationship ADD CONSTRAINT FK_BFC4C273247D928B FOREIGN KEY (party_a_id) REFERENCES party (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE party_relationship ADD CONSTRAINT FK_BFC4C27336C83D65 FOREIGN KEY (party_b_id) REFERENCES party (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party_relationship DROP CONSTRAINT FK_BFC4C273247D928B');
        $this->addSql('ALTER TABLE party_relationship DROP CONSTRAINT FK_BFC4C27336C83D65');
        $this->addSql('DROP TABLE party');
        $this->addSql('DROP TABLE party_relationship');
        $this->addSql('DROP TABLE party_role');
    }
}
