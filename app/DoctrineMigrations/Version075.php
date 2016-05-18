<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version075 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE account DROP opening_date_explanation');
        $this->addSql('ALTER TABLE account DROP closing_balance_explanation');
        $this->addSql('ALTER TABLE account DROP opening_date');
        $this->addSql('ALTER TABLE account DROP closing_date');
        $this->addSql('ALTER TABLE account DROP closing_date_explanation');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE account ADD opening_date_explanation TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD closing_balance_explanation TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD opening_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD closing_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD closing_date_explanation TEXT DEFAULT NULL');
    }
}
