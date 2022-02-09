<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220209194742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE car_type_active_counter (car_class VARCHAR(255) NOT NULL, active_cars_counter INT NOT NULL, PRIMARY KEY(car_class))');
        $this->addSql('ALTER TABLE car_type DROP active_cars_counter');
        $this->addSql('ALTER TABLE transit ALTER drivers_fee TYPE INT');
        $this->addSql('ALTER TABLE transit ALTER drivers_fee DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN transit.drivers_fee IS \'(DC2Type:money)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE car_type_active_counter');
        $this->addSql('ALTER TABLE car_type ADD active_cars_counter INT NOT NULL');
        $this->addSql('ALTER TABLE transit ALTER drivers_fee TYPE INT');
        $this->addSql('ALTER TABLE transit ALTER drivers_fee DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN transit.drivers_fee IS NULL');
    }
}
