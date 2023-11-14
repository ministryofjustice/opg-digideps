<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE review_checklist (id SERIAL NOT NULL, report_id INT NOT NULL, submitted_by INT DEFAULT NULL, last_modified_by INT DEFAULT NULL, answers JSON DEFAULT NULL, decision VARCHAR(30) DEFAULT NULL, submitted_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB98F62E4BD2A4C0 ON review_checklist (report_id)');
        $this->addSql('CREATE INDEX IDX_CB98F62E641EE842 ON review_checklist (submitted_by)');
        $this->addSql('CREATE INDEX IDX_CB98F62E65CF370E ON review_checklist (last_modified_by)');
        $this->addSql('ALTER TABLE review_checklist ADD CONSTRAINT FK_CB98F62E4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_checklist ADD CONSTRAINT FK_CB98F62E641EE842 FOREIGN KEY (submitted_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review_checklist ADD CONSTRAINT FK_CB98F62E65CF370E FOREIGN KEY (last_modified_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE review_checklist');
    }
}
