<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version042 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE access_token DROP CONSTRAINT fk_b6a2dd685df1bcb8');
        $this->addSql('ALTER TABLE refresh_token DROP CONSTRAINT fk_c74f21955df1bcb8');
        $this->addSql('ALTER TABLE auth_code DROP CONSTRAINT fk_5933d02c5df1bcb8');
        $this->addSql('DROP TABLE access_token');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE auth_code');
        $this->addSql('DROP TABLE oauth2_client');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE access_token (id INT NOT NULL, oauth2_client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_b6a2dd68a76ed395 ON access_token (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_b6a2dd685f37a13b ON access_token (token)');
        $this->addSql('CREATE INDEX idx_b6a2dd685df1bcb8 ON access_token (oauth2_client_id)');
        $this->addSql('CREATE TABLE refresh_token (id INT NOT NULL, oauth2_client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_c74f21955df1bcb8 ON refresh_token (oauth2_client_id)');
        $this->addSql('CREATE INDEX idx_c74f2195a76ed395 ON refresh_token (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_c74f21955f37a13b ON refresh_token (token)');
        $this->addSql('CREATE TABLE auth_code (id INT NOT NULL, oauth2_client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri TEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_5933d02ca76ed395 ON auth_code (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_5933d02c5f37a13b ON auth_code (token)');
        $this->addSql('CREATE INDEX idx_5933d02c5df1bcb8 ON auth_code (oauth2_client_id)');
        $this->addSql('CREATE TABLE oauth2_client (id INT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris TEXT NOT NULL, secret VARCHAR(255) NOT NULL, allowed_grant_types TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN oauth2_client.redirect_uris IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_client.allowed_grant_types IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT fk_b6a2dd685df1bcb8 FOREIGN KEY (oauth2_client_id) REFERENCES oauth2_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT fk_b6a2dd68a76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT fk_c74f21955df1bcb8 FOREIGN KEY (oauth2_client_id) REFERENCES oauth2_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT fk_c74f2195a76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE auth_code ADD CONSTRAINT fk_5933d02c5df1bcb8 FOREIGN KEY (oauth2_client_id) REFERENCES oauth2_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE auth_code ADD CONSTRAINT fk_5933d02ca76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
