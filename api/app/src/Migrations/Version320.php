<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1693';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE income_received_on_clients_behalf DROP CONSTRAINT FK_2F551CA35064A0FF');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ADD CONSTRAINT FK_2F551CA35064A0FF FOREIGN KEY (client_benefits_check_id) REFERENCES client_benefits_check (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT FK_8A8E0C134BD2A4C0');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT FK_8A8E0C134BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy DROP CONSTRAINT FK_994DD8A9A8D7D89C');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT FK_994DD8A9A8D7D89C FOREIGN KEY (court_order_id) REFERENCES court_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE income_received_on_clients_behalf DROP CONSTRAINT fk_2f551ca35064a0ff');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ADD CONSTRAINT fk_2f551ca35064a0ff FOREIGN KEY (client_benefits_check_id) REFERENCES client_benefits_check (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT fk_8a8e0c134bd2a4c0');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT fk_8a8e0c134bd2a4c0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_deputy DROP CONSTRAINT fk_994dd8a9a8d7d89c');
        $this->addSql('ALTER TABLE court_order_deputy ADD CONSTRAINT fk_994dd8a9a8d7d89c FOREIGN KEY (court_order_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
