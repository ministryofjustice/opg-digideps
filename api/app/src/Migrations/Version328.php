<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE electronic_device_type (id UUID NOT NULL, desktop BOOLEAN DEFAULT NULL, laptop BOOLEAN DEFAULT NULL, mobile BOOLEAN DEFAULT NULL, tablet BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN electronic_device_type.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_research_response ADD electronicDeviceType_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN user_research_response.electronicDeviceType_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71A8EA2B602 FOREIGN KEY (electronicDeviceType_id) REFERENCES electronic_device_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B9FE71A8EA2B602 ON user_research_response (electronicDeviceType_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT FK_3B9FE71A8EA2B602');
        $this->addSql('DROP TABLE electronic_device_type');
        $this->addSql('DROP INDEX UNIQ_3B9FE71A8EA2B602');
        $this->addSql('ALTER TABLE user_research_response DROP electronicDeviceType_id');
    }
}
