<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'sirius_client';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staging.sirius_client (case_number VARCHAR(20) NOT NULL, client_first_name VARCHAR(50) NOT NULL, client_last_name VARCHAR(50) NOT NULL, client_date_of_birth DATE DEFAULT NULL, client_address1 VARCHAR(200) NOT NULL, client_address2 VARCHAR(200) NOT NULL, client_address3 VARCHAR(200) NOT NULL, client_address4 VARCHAR(200) NOT NULL, client_address5 VARCHAR(200) NOT NULL, client_post_code VARCHAR(10) NOT NULL, local_id INT DEFAULT NULL, PRIMARY KEY(case_number))');
        $this->addSql('COMMENT ON COLUMN staging.sirius_client.client_date_of_birth IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE staging.sirius_client');
    }
}
