<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version264 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a created_at and updated_at column to reports, users, clients, and named deputy tables.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // report
        $this->addSql('ALTER TABLE report ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE report RENAME COLUMN last_edit TO updated_at');

        // users
        $this->addSql('ALTER TABLE dd_user ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        // client
        $this->addSql('ALTER TABLE client ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE client RENAME COLUMN last_edit TO updated_at');

        // named deputy
        $this->addSql('ALTER TABLE named_deputy ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE named_deputy ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        // account
        $this->addSql('ALTER TABLE odr_account ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE account RENAME COLUMN last_edit TO updated_at');

        // pre registration
        $this->addSql('ALTER TABLE pre_registration DROP uploaded_at'); // replace by created_at
        $this->addSql('ALTER TABLE pre_registration ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        // asset
        $this->addSql('ALTER TABLE odr_asset ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE odr_asset RENAME COLUMN last_edit TO updated_at');
        $this->addSql('ALTER TABLE asset ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE asset RENAME COLUMN last_edit TO updated_at');

        // contact
        $this->addSql('ALTER TABLE contact ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE contact RENAME COLUMN last_edit TO updated_at');

        // decision
        $this->addSql('ALTER TABLE decision ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE decision ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP created_at');
        $this->addSql('ALTER TABLE report RENAME COLUMN updated_at TO last_edit');

        $this->addSql('ALTER TABLE dd_user DROP created_at');
        $this->addSql('ALTER TABLE dd_user DROP updated_at');

        $this->addSql('ALTER TABLE client DROP created_at');
        $this->addSql('ALTER TABLE client RENAME COLUMN updated_at TO last_edit');

        $this->addSql('ALTER TABLE named_deputy DROP created_at');
        $this->addSql('ALTER TABLE named_deputy DROP updated_at');

        $this->addSql('ALTER TABLE odr_asset DROP created_at');
        $this->addSql('ALTER TABLE odr_asset RENAME COLUMN updated_at TO last_edit');
        $this->addSql('ALTER TABLE asset DROP created_at');
        $this->addSql('ALTER TABLE asset RENAME COLUMN updated_at TO last_edit');

        $this->addSql('ALTER TABLE odr_account DROP updated_at');
        $this->addSql('ALTER TABLE account RENAME COLUMN updated_at TO last_edit');

        $this->addSql('ALTER TABLE pre_registration ADD uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE pre_registration DROP created_at');

        $this->addSql('ALTER TABLE contact DROP created_at');
        $this->addSql('ALTER TABLE contact RENAME COLUMN updated_at TO last_edit');
    }
}
