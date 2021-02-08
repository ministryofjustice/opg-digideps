<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version237 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create 1-to-1 relation between CourtOrder and NDR. Drop NULL constraints on CourtOrder and CourtOrderDeputy tables';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE odr ADD court_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD CONSTRAINT FK_350EBBCA8D7D89C FOREIGN KEY (court_order_id) REFERENCES court_order (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_350EBBCA8D7D89C ON odr (court_order_id)');
        $this->addSql('ALTER TABLE court_order_deputy ALTER deputynumber DROP NOT NULL');
        $this->addSql('ALTER TABLE court_order ALTER type DROP NOT NULL');
        $this->addSql('ALTER TABLE court_order ALTER order_date DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE odr DROP CONSTRAINT FK_350EBBCA8D7D89C');
        $this->addSql('DROP INDEX UNIQ_350EBBCA8D7D89C');
        $this->addSql('ALTER TABLE odr DROP court_order_id');
        $this->addSql('ALTER TABLE court_order ALTER type SET NOT NULL');
        $this->addSql('ALTER TABLE court_order ALTER order_date SET NOT NULL');
        $this->addSql('ALTER TABLE court_order_deputy ALTER deputyNumber SET NOT NULL');
    }
}
