<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version071 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report ADD agreed_behalf_deputy VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD agreed_behalf_deputy_explanation TEXT DEFAULT NULL');

//        'only_deputy'=>'I am the only deputy',
//        'more_deputies_behalf'=>'There is more than one deputy, and I’m signing on everyone’s behalf',
//        'more_deputies_not_behalf'

        // the ones with explanation "only" => 1st type
        $this->addSql('UPDATE report '
                ." SET agreed_behalf_deputy ='only_deputy' "
                .' WHERE submitted=true ' // only submitted reports
." AND reason_not_all_agreed ILIKE '%only %' OR reason_not_all_agreed ILIKE '% sole %' OR reason_not_all_agreed ILIKE '%there are no others%'");

        // for all the other explanations => 3rd type  along with explanation
        $this->addSql('UPDATE report '
                ." SET agreed_behalf_deputy ='more_deputies_not_behalf' "
                .' WHERE agreed_behalf_deputy IS NULL ' // skip already done
.' AND submitted=true ' // only submitted reports
.' AND reason_not_all_agreed IS NOT NULL');

        // the ones agreed (and not set before) => 2nd type (cannot be 1st, see 1st query)
        $this->addSql('UPDATE report '
                ." SET agreed_behalf_deputy ='more_deputies_behalf' "
                 .' WHERE agreed_behalf_deputy IS NULL ' // skip already done
.' AND submitted=true '// only submitted reports
.' AND all_agreed = true AND agreed_behalf_deputy IS NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report DROP agreed_behalf_deputy');
        $this->addSql('ALTER TABLE report DROP agreed_behalf_deputy_explanation');
    }
}
