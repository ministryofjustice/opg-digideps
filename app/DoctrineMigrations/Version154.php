<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version154 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE note DROP CONSTRAINT fk_cfbdfa144bd2a4c0');
        $this->addSql('ALTER TABLE note DROP CONSTRAINT FK_CFBDFA1465CF370E');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1465CF370E FOREIGN KEY (last_modified_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_contact DROP CONSTRAINT FK_1E5FA245DE12AB56');
        $this->addSql('ALTER TABLE client_contact ADD CONSTRAINT FK_1E5FA245DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE note DROP CONSTRAINT FK_CFBDFA1419EB6921');
        $this->addSql('ALTER TABLE note DROP CONSTRAINT fk_cfbdfa1465cf370e');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT fk_cfbdfa144bd2a4c0 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT fk_cfbdfa1465cf370e FOREIGN KEY (last_modified_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_contact DROP CONSTRAINT fk_1e5fa245de12ab56');
        $this->addSql('ALTER TABLE client_contact ADD CONSTRAINT fk_1e5fa245de12ab56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ALTER wish_to_provide_documentation TYPE VARCHAR(3)');
    }
}
