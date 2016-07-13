<?php

namespace Application\Migrations;

use AppBundle\Entity\Client;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * debts fix existing reports
 */
class Version079 extends AbstractMigration implements ContainerAwareInterface
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
        ini_set('memory_limit','1024M');

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $em =$this->container->get('em');

        $qb = $em->createQuery(
            "SELECT c,r FROM AppBundle\Entity\Client c
                LEFT JOIN c.reports r");
        $clients = $qb->getResult();
        foreach($clients as $client) { /* @var $client Client */
            $submitted = 0;
            $unSubmitted = 0;
            $lastReport = null;
            foreach($client->getReports() as $report) {
                if ($report->getSubmitted()) {
                    $submitted++;

                } else {
                    $unSubmitted++;
                }
                $lastReport = $report;
            }

            if ($submitted > 0 && $unSubmitted===0) { // if there is a submitted one, but no currenct
                $nextYearReport =  $em->getRepository('AppBundle\Entity\Report')
                    ->createNextYearReport($lastReport);

                echo "created new report for ".$client->getCaseNumber()."\n";
            }

        }
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
