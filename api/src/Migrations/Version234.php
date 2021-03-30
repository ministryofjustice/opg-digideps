<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version234 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE court_order_address (id SERIAL NOT NULL, court_order_deputy_id INT DEFAULT NULL, addressLine1 VARCHAR(255) DEFAULT NULL, addressLine2 VARCHAR(255) DEFAULT NULL, addressLine3 VARCHAR(255) DEFAULT NULL, town VARCHAR(255) DEFAULT NULL, county VARCHAR(255) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C2454DABF164D17C ON court_order_address (court_order_deputy_id)');
        $this->addSql('CREATE TABLE court_order_deputy (id SERIAL NOT NULL, court_order_id INT NOT NULL, user_id INT DEFAULT NULL, organisation_id INT DEFAULT NULL, deputyNumber VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, dob DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_994DD8A9A8D7D89C ON court_order_deputy (court_order_id)');
        $this->addSql('CREATE INDEX IDX_994DD8A9A76ED395 ON court_order_deputy (user_id)');
        $this->addSql('CREATE INDEX IDX_994DD8A99E6B1585 ON court_order_deputy (organisation_id)');
        $this->addSql('ALTER TABLE court_order_address ADD CONSTRAINT FK_C2454DABF164D17C FOREIGN KEY (court_order_deputy_id) REFERENCES court_order_deputy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT FK_994DD8A9A8D7D89C FOREIGN KEY (court_order_id) REFERENCES court_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT FK_994DD8A9A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT FK_994DD8A99E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE court_order_address DROP CONSTRAINT FK_C2454DABF164D17C');
        $this->addSql('DROP TABLE court_order_address');
        $this->addSql('DROP TABLE court_order_deputy');
    }
}
