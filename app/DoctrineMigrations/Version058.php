<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use AppBundle\Service\DataMigration\AccountMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version058 extends AbstractMigration implements ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
        $reports = $pdo->query("SELECT r.id as reportId, t.amount as incomeFromInvAmount from report r LEFT JOIN transaction t ON t.report_id=r.id WHERE t.transaction_type_id='income-from-investments'")->fetchAll();

        $this->addSql("UPDATE transaction_type SET display_order = display_order * 10");

        $this->addSql("INSERT INTO transaction_type(id, has_more_details, display_order, category, type) VALUES('other-incomes', false, 65, 'income-and-earnings', 'in')");
        
        // ADD new transaction to all the report. copy amount from income-from-investments transaction (will be deleted)
        $stmt = $pdo->prepare(
                "INSERT INTO transaction(report_id, transaction_type_id, amount, more_details)"
                . " VALUES(:report_id, :transaction_type_id, :amount, :md)");
        foreach ($reports as $report) {
            $params = [
                ':report_id' => $report['reportId'],
                ':transaction_type_id' => 'other-incomes',
                ':amount' => $report['incomeFromInvAmount'], //could be null
                ':md' => '',
            ];
            $stmt->execute($params);
        }
        
        $this->addSql("DELETE FROM transaction WHERE transaction_type_id='income-from-investments'"); //TODO migrate 
        $this->addSql("DELETE FROM transaction_type WHERE id='income-from-investments'"); //TODO migrate 
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
