<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use AppBundle\Service\DataMigration\AccountMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version058 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

//        $em = $this->container->get('em');
//        $am = new AccountMigration($em->getConnection());
//        $am->migrateAll();
        
        $pdo = $this->container->get('em')->getConnection();
        $reports = $pdo->query('SELECT * from report')->fetchAll();
        
        $this->addSql("UPDATE transaction_type SET display_order = display_order * 10");
        
        $this->addSql("INSERT INTO transaction_type(id, has_more_details, display_order, category, type) VALUES('other-incomes', false, 65, 'income-and-earnings', 'in')");
        //TODO add to all the report, transaction table
        
        //TODO: copy values income-from-investments -> other-incomes
        $this->addSql("DELETE FROM transaction WHERE transaction_type_id='income-from-investments'"); //TODO migrate 
        $this->addSql("DELETE FROM transaction_type WHERE id='income-from-investments'"); //TODO migrate 
        
        //TODO
        
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('');


    }
}
