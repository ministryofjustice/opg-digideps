<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('DELETE FROM report_submission WHERE report_id IS NULL');
        //$this->addSql('ALTER TABLE report_submission ALTER report_id SET NOT NULL');
        //$this->addSql('CREATE TABLE staging.sirius_organisation (domain VARCHAR(60) NOT NULL, name VARCHAR(256) DEFAULT NULL, local_id INT DEFAULT NULL, PRIMARY KEY(domain))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        //$this->addSql('DROP TABLE staging.sirius_organisation');
        //$this->addSql('ALTER TABLE report_submission ALTER report_id DROP NOT NULL');
    }
}
