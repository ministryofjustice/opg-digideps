<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT FK_28FA6B9FA76ED395');
        $this->addSql('ALTER TABLE deputy ADD organisation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT FK_28FA6B9F9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT FK_28FA6B9FA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_28FA6B9F9E6B1585 ON deputy (organisation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT FK_28FA6B9F9E6B1585');
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT fk_28fa6b9fa76ed395');
        $this->addSql('DROP INDEX IDX_28FA6B9F9E6B1585');
        $this->addSql('ALTER TABLE deputy DROP organisation_id');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT fk_28fa6b9fa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
