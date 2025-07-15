<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE court_order_deputy DROP CONSTRAINT FK_994DD8A94B6F93BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE court_order_deputy ADD CONSTRAINT FK_994DD8A94B6F93BB FOREIGN KEY (deputy_id) REFERENCES deputy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE court_order_deputy DROP CONSTRAINT fk_994dd8a94b6f93bb
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE court_order_deputy ADD CONSTRAINT fk_994dd8a94b6f93bb FOREIGN KEY (deputy_id) REFERENCES deputy (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
