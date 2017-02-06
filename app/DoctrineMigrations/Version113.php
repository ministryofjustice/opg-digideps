<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version113 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // copy report.further_information -> report.action_more_info*
        $this->addSql("UPDATE report SET action_more_info='yes', action_more_info_details=further_information  WHERE char_length(further_information) > 3");

        // submitted report with no further info will have "no" for the same question
        $this->addSql("UPDATE report SET action_more_info='no'  WHERE char_length(further_information) < 3 AND submitted=true");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
