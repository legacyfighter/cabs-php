<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220123120851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN driver_fee.min IS \'(DC2Type:money)\'');
        $this->addSql('COMMENT ON COLUMN transit.price IS \'(DC2Type:money)\'');
        $this->addSql('COMMENT ON COLUMN transit.estimated_price IS \'(DC2Type:money)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN transit.price IS NULL');
        $this->addSql('COMMENT ON COLUMN transit.estimated_price IS NULL');
        $this->addSql('COMMENT ON COLUMN driver_fee.min IS NULL');
    }
}
