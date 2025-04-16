<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version296 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create selected candidates staging table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA IF NOT EXISTS staging');
        $this->addSql(<<<'SQL'
            CREATE TABLE staging.selectedCandidates (id SERIAL NOT NULL, order_uid VARCHAR(30) NOT NULL, deputy_uid VARCHAR(30) NOT NULL, action VARCHAR(30) NOT NULL, order_status VARCHAR(30) DEFAULT NULL, order_type VARCHAR(5) DEFAULT NULL, report_type VARCHAR(30) DEFAULT NULL, order_made_date VARCHAR(30) DEFAULT NULL, deputy_type VARCHAR(30) DEFAULT NULL, deputy_status_on_order BOOLEAN DEFAULT NULL, is_hybrid VARCHAR(30) DEFAULT NULL, order_id INT DEFAULT NULL, client_id INT DEFAULT NULL, report_id INT DEFAULT NULL, deputy_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE staging.selectedCandidates
        SQL);
    }
}
