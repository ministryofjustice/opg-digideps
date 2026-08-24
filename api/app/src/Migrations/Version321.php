<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE report_cleanup_problem_id_seq');
        $this->addSql('SELECT setval(\'report_cleanup_problem_id_seq\', (SELECT MAX(id) FROM report_cleanup_problem))');
        $this->addSql('ALTER TABLE report_cleanup_problem ALTER id SET DEFAULT nextval(\'report_cleanup_problem_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report_cleanup_problem ALTER id DROP DEFAULT');
    }
}
