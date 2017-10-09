<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version152 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F778419EB6921');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE decision DROP CONSTRAINT FK_84ACBE484BD2A4C0');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE484BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE contact DROP CONSTRAINT FK_4C62E6384BD2A4C0');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E6384BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE account DROP CONSTRAINT FK_7D3656A44BD2A4C0');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A44BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE money_transfer DROP CONSTRAINT FK_A15E50EEB0CF99BD');
        $this->addSql('ALTER TABLE money_transfer DROP CONSTRAINT FK_A15E50EEBC58BDC7');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEB0CF99BD FOREIGN KEY (from_account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEBC58BDC7 FOREIGN KEY (to_account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE safeguarding DROP CONSTRAINT FK_8C7877184BD2A4C0');
        $this->addSql('ALTER TABLE safeguarding ADD CONSTRAINT FK_8C7877184BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE mental_capacity DROP CONSTRAINT FK_9564F4954BD2A4C0');
        $this->addSql('ALTER TABLE mental_capacity ADD CONSTRAINT FK_9564F4954BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE money_transaction DROP CONSTRAINT FK_D21254E24BD2A4C0');
        $this->addSql('ALTER TABLE money_transaction ADD CONSTRAINT FK_D21254E24BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE action DROP CONSTRAINT fk_281efba84bd2a4c0');
        $this->addSql('ALTER TABLE action ADD CONSTRAINT FK_47CC8C924BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE asset DROP CONSTRAINT FK_2AF5A5C4BD2A4C0');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE expense DROP CONSTRAINT FK_2D3A8DA64BD2A4C0');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA64BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE gift DROP CONSTRAINT FK_A47C990D4BD2A4C0');
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990D4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A764BD2A4C0');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A764BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE money_transaction_short DROP CONSTRAINT FK_E712D1F64BD2A4C0');
        $this->addSql('ALTER TABLE money_transaction_short ADD CONSTRAINT FK_E712D1F64BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE lifestyle DROP CONSTRAINT FK_D63A75CF4BD2A4C0');
        $this->addSql('ALTER TABLE lifestyle ADD CONSTRAINT FK_D63A75CF4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE odr DROP CONSTRAINT FK_350EBBC19EB6921');
        $this->addSql('ALTER TABLE odr ADD CONSTRAINT FK_350EBBC19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

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

        $this->addSql('ALTER TABLE client_contact DROP CONSTRAINT FK_1E5FA24519EB6921');
        $this->addSql('ALTER TABLE client_contact ADD CONSTRAINT FK_1E5FA24519EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE money_transfer DROP CONSTRAINT FK_A15E50EE4BD2A4C0');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EE4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

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
