<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220123224330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transit ADD tariff_km_rate DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE transit ADD tariff_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE transit ADD tariff_base_fee INT NOT NULL');
        $this->addSql('ALTER TABLE transit DROP factor');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transit ADD factor INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transit DROP tariff_km_rate');
        $this->addSql('ALTER TABLE transit DROP tariff_name');
        $this->addSql('ALTER TABLE transit DROP tariff_base_fee');
    }
}
