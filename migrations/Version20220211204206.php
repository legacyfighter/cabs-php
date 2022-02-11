<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220211204206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awarded_miles DROP expiration_date');
        $this->addSql('ALTER TABLE awarded_miles DROP is_special');
        $this->addSql('ALTER TABLE awarded_miles DROP miles');
        $this->addSql('ALTER TABLE awarded_miles ADD miles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awarded_miles ADD expiration_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE awarded_miles ADD is_special BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE awarded_miles DROP miles');
        $this->addSql('ALTER TABLE awarded_miles ADD miles INT NOT NULL');
        $this->addSql('COMMENT ON COLUMN awarded_miles.expiration_date IS \'(DC2Type:datetime_immutable)\'');
    }
}
