<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report DROP CONSTRAINT fk_c42f7784a8d7d89c');
        $this->addSql('ALTER TABLE odr DROP CONSTRAINT fk_350ebbca8d7d89c');
        $this->addSql('ALTER TABLE court_order_deputy DROP CONSTRAINT fk_994dd8a9a8d7d89c');
        $this->addSql('ALTER TABLE court_order_address DROP CONSTRAINT fk_c2454dabf164d17c');
        $this->addSql('DROP SEQUENCE court_order_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE court_order_address_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE court_order_deputy_id_seq CASCADE');
        $this->addSql('DROP TABLE court_order');
        $this->addSql('DROP TABLE court_order_deputy');
        $this->addSql('DROP TABLE court_order_address');
        $this->addSql('DROP INDEX uniq_350ebbca8d7d89c');
        $this->addSql('ALTER TABLE odr DROP court_order_id');
        $this->addSql('DROP INDEX idx_c42f7784a8d7d89c');
        $this->addSql('ALTER TABLE report DROP court_order_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE court_order_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE court_order_address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE court_order_deputy_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE court_order (id SERIAL NOT NULL, client_id INT NOT NULL, type VARCHAR(4) DEFAULT NULL, supervision_level VARCHAR(8) DEFAULT NULL, order_date DATE DEFAULT NULL, case_number VARCHAR(16) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e824c019eb6921 ON court_order (client_id)');
        $this->addSql('CREATE TABLE court_order_deputy (id SERIAL NOT NULL, court_order_id INT NOT NULL, user_id INT DEFAULT NULL, organisation_id INT DEFAULT NULL, deputynumber VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, dob DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_994dd8a99e6b1585 ON court_order_deputy (organisation_id)');
        $this->addSql('CREATE INDEX idx_994dd8a9a76ed395 ON court_order_deputy (user_id)');
        $this->addSql('CREATE INDEX idx_994dd8a9a8d7d89c ON court_order_deputy (court_order_id)');
        $this->addSql('CREATE TABLE court_order_address (id SERIAL NOT NULL, court_order_deputy_id INT DEFAULT NULL, addressline1 VARCHAR(255) DEFAULT NULL, addressline2 VARCHAR(255) DEFAULT NULL, addressline3 VARCHAR(255) DEFAULT NULL, town VARCHAR(255) DEFAULT NULL, county VARCHAR(255) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_c2454dabf164d17c ON court_order_address (court_order_deputy_id)');
        $this->addSql('ALTER TABLE court_order ADD CONSTRAINT fk_e824c019eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT fk_994dd8a9a8d7d89c FOREIGN KEY (court_order_id) REFERENCES court_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT fk_994dd8a9a76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT fk_994dd8a99e6b1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_address ADD CONSTRAINT fk_c2454dabf164d17c FOREIGN KEY (court_order_deputy_id) REFERENCES court_order_deputy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD court_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT fk_c42f7784a8d7d89c FOREIGN KEY (court_order_id) REFERENCES court_order (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c42f7784a8d7d89c ON report (court_order_id)');
        $this->addSql('ALTER TABLE odr ADD court_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD CONSTRAINT fk_350ebbca8d7d89c FOREIGN KEY (court_order_id) REFERENCES court_order (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_350ebbca8d7d89c ON odr (court_order_id)');
    }
}
