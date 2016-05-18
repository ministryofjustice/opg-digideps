<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version059 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function addNewTransaction($name, $displayOrder, $hasMoreDetails, $copValuesFrom)
    {
        $pdo = $this->container->get('em')->getConnection();

        // add new "total" other-incomes in the right position
        $pdo->query('INSERT INTO transaction_type(id, has_more_details, display_order, category, type) '
                ."VALUES('$name', ".($hasMoreDetails ? 'true' : 'false').", $displayOrder, 'income-and-earnings', 'in')");

        // ADD new transaction to all the report. copy amount from $copValuesFrom transaction (will be deleted)
        $reports = $pdo->query("SELECT r.id as report_id, t.amount as income_from_inv_amount from report r LEFT JOIN transaction t ON t.report_id=r.id WHERE t.transaction_type_id='$copValuesFrom'")->fetchAll();

        // for each report, add the new transaction, and copy value from "income-and-earnings"
        $stmt = $pdo->prepare(
                'INSERT INTO transaction(report_id, transaction_type_id, amount, more_details)'
                .' VALUES(:report_id, :transaction_type_id, :amount, :md)');
        foreach ($reports as $report) {
            $params = [
                ':report_id' => $report['report_id'],
                ':transaction_type_id' => $name,
                ':amount' => $report['income_from_inv_amount'], //could be null
                ':md' => '',
            ];
            $stmt->execute($params);
        }

        $pdo->query("DELETE FROM transaction WHERE transaction_type_id='$copValuesFrom'");
        $pdo->query("DELETE FROM transaction_type WHERE id='$copValuesFrom'");
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addNewTransaction('other-incomes', 65, true, 'income-from-investments');

        $this->addSql('SELECT MAX(version) from migrations');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addNewTransaction('income-from-investments', 40, false, 'other-incomes');

        $this->addSql('SELECT MAX(version) from migrations');
    }
}
