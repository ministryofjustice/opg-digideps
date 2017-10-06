<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version168 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE odr_asset DROP CONSTRAINT FK_73D022FB7CE4B994');
        $this->addSql('ALTER TABLE odr_asset ADD CONSTRAINT FK_73D022FB7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_account DROP CONSTRAINT FK_C2AEF4FB7CE4B994');
        $this->addSql('ALTER TABLE odr_account ADD CONSTRAINT FK_C2AEF4FB7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_debt DROP CONSTRAINT FK_154224C77CE4B994');
        $this->addSql('ALTER TABLE odr_debt ADD CONSTRAINT FK_154224C77CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_one_off DROP CONSTRAINT FK_5941831F7CE4B994');
        $this->addSql('ALTER TABLE odr_income_one_off ADD CONSTRAINT FK_5941831F7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_expense DROP CONSTRAINT fk_2d3a8da67ce4b994');
        $this->addSql('ALTER TABLE odr_expense ADD CONSTRAINT FK_92A22FF97CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_visits_care DROP CONSTRAINT FK_9239DE877CE4B994');
        $this->addSql('ALTER TABLE odr_visits_care ADD CONSTRAINT FK_9239DE877CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_state_benefit DROP CONSTRAINT FK_1C1A04A77CE4B994');
        $this->addSql('ALTER TABLE odr_income_state_benefit ADD CONSTRAINT FK_1C1A04A77CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}
