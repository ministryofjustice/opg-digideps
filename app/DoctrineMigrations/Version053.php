<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * add transactions to reports already existing before branch accounts_mk2 is merged
 */
class Version053 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('SELECT COUNT(*) FROM migrations'); //just to avoid warning
    }


    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $pdo = $em->getConnection(); /* @var $pdo \PDO */

        $types = $pdo->query('SELECT * FROM transaction_type ORDER BY display_order ASC')->fetchAll();
        $reports = $pdo->query('SELECT r.id, COUNT(t.id)  from report r LEFT JOIN transaction t on t.report_id = r.id GROUP BY r.id')->fetchAll();

        $changed = 0;
        $stmt = $pdo->prepare("INSERT INTO transaction(report_id, transaction_type_id) VALUES(:id, :type)");
        foreach ($reports as $report) {
            $reportId = $report['id'];
            if ($report['count'] == 0) {
                $this->write("Adding transaction to report $reportId");
                foreach ($types as $type) {
                    $stmt->bindParam(':id', $report['id'], \PDO::PARAM_INT);
                    $stmt->bindParam(':type', $type['id'], \PDO::PARAM_STR);
                    $stmt->execute();
                    $changed++;
                }
            }
        }

        $this->write(__CLASS__ . ": added transactions to $changed reports");
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
