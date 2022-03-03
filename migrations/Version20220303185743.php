<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220303185743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE travelled_distance (interval_id UUID NOT NULL, driver_id INT NOT NULL, last_latitude DOUBLE PRECISION NOT NULL, last_longitude DOUBLE PRECISION NOT NULL, distance DOUBLE PRECISION NOT NULL, beginning TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "end" TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(interval_id))');
        $this->addSql('COMMENT ON COLUMN travelled_distance.interval_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN travelled_distance.distance IS \'(DC2Type:distance)\'');
        $this->addSql('COMMENT ON COLUMN travelled_distance.beginning IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN travelled_distance."end" IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE travelled_distance');
    }
}
