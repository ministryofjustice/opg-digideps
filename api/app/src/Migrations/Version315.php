<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixup Version313';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE staging.lay_ingest_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE staging.pa_pro_ingest_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE staging.lay_ingest_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE staging.pa_pro_ingest_id_seq CASCADE');
    }
}
