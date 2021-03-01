<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version248 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add research_type and user_research_submission tables to handle UR feedback post report submission.';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE research_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_research_submission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE research_type (id INT NOT NULL, user_research_submission_id INT NOT NULL, surveys BOOLEAN NOT NULL, videoCall BOOLEAN NOT NULL, phone BOOLEAN NOT NULL, inPerson BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_research_submission (id INT NOT NULL, research_type_id INT NOT NULL, deputyshipLength VARCHAR(255) NOT NULL, hasAccessToVideoCallDevice BOOLEAN NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE research_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_research_submission_id_seq CASCADE');
        $this->addSql('DROP TABLE research_type');
        $this->addSql('DROP TABLE user_research_submission');
    }
}
