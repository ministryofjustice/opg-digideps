<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DELETE FROM migrations WHERE version IN '
            ."('001','002','003','004','005','006','007','008','009','010',"
            ."'011','012','013','014','015','016','017','018','019','020',"
            ."'021','022','023','024','025','026','027','028','029','030',"
            ."'031','032','033','034','035','036','037','038');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
