<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version288 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates link table between court orders and deputies';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE court_order_deputy (court_order_id INT NOT NULL, deputy_id INT NOT NULL, discharged BOOLEAN NOT NULL, PRIMARY KEY(court_order_id, deputy_id))');
        $this->addSql('CREATE INDEX IDX_994DD8A9A8D7D89C ON court_order_deputy (court_order_id)');
        $this->addSql('CREATE INDEX IDX_994DD8A94B6F93BB ON court_order_deputy (deputy_id)');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT FK_994DD8A9A8D7D89C FOREIGN KEY (court_order_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT FK_994DD8A94B6F93BB FOREIGN KEY (deputy_id) REFERENCES deputy (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order_deputy DROP CONSTRAINT FK_994DD8A9A8D7D89C');
        $this->addSql('ALTER TABLE court_order_deputy DROP CONSTRAINT FK_994DD8A94B6F93BB');
        $this->addSql('DROP TABLE court_order_deputy');
    }
}
