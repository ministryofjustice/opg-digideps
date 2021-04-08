<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version233 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create court_order table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE court_order (id SERIAL NOT NULL, client_id INT NOT NULL, type VARCHAR(4) NOT NULL, supervision_level VARCHAR(8), order_date DATE NOT NULL, case_number VARCHAR(16) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E824C019EB6921 ON court_order (client_id)');
        $this->addSql('ALTER TABLE court_order ADD CONSTRAINT FK_E824C019EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD court_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A8D7D89C FOREIGN KEY (court_order_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C42F7784A8D7D89C ON report (court_order_id)');
        $this->addSql('ALTER TABLE casrec ADD order_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE court_order');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784A8D7D89C');
        $this->addSql('DROP INDEX IDX_C42F7784A8D7D89C');
        $this->addSql('ALTER TABLE report DROP court_order_id');
        $this->addSql('ALTER TABLE casrec DROP order_date');
    }
}
