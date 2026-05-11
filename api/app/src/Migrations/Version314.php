<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'sirius_deputy';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staging.sirius_deputy (deputy_uid VARCHAR(20) NOT NULL, deputy_type VARCHAR(3) NOT NULL, deputy_email VARCHAR(60) DEFAULT NULL, deputy_organisation VARCHAR(100) DEFAULT NULL, deputy_first_name VARCHAR(100) NOT NULL, deputy_last_name VARCHAR(100) NOT NULL, deputy_address1 VARCHAR(200) NOT NULL, deputy_address2 VARCHAR(200) NOT NULL, deputy_address3 VARCHAR(200) NOT NULL, deputy_address4 VARCHAR(200) NOT NULL, deputy_address5 VARCHAR(200) NOT NULL, deputy_post_code VARCHAR(10) NOT NULL, local_id INT DEFAULT NULL, PRIMARY KEY(deputy_uid))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE staging.sirius_deputy');
    }
}
