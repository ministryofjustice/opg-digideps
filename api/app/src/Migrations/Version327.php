<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1763';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE report_cleanup_problem_id_seq CASCADE');
        $this->addSql('DROP TABLE report_cleanup_problem');
        $this->addSql('DROP TABLE report_cleanup_action');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE report_cleanup_problem_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE report_cleanup_problem (id SERIAL NOT NULL, client_id INT DEFAULT NULL, report_id INT DEFAULT NULL, problem INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE report_cleanup_action (report_id INT NOT NULL, order_id INT NOT NULL, keep BOOLEAN NOT NULL, PRIMARY KEY(report_id, order_id))');
    }
}
