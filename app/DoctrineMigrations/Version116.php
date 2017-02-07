<?php

namespace Application\Migrations;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\ReportStatusService;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version116 extends AbstractMigration implements ContainerAwareInterface
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
        $em = $this->container->get('em');
        //['caseNumber' => '12814692']
        foreach ($em->getRepository(Client::class)->findAll() as $client) {
            $activeReports = $client->getReports()->filter(function ($report) {
                return !$report->getSubmitted();
            });
            if (count($activeReports) > 1) {
                $emptyReports = $activeReports->filter(function ($report) {
                    /* @var $report Report */
                    $rss = new ReportStatusService($report);

                    return $rss->getStatus() == 'notStarted';
                });

                if (count($activeReports) > 1) {
                    echo "client " . $client->getCaseNumber() . ":"
                        . count($activeReports) . " active reports, of which "
                        . count($emptyReports) . " empty:";
                }

                if (count($activeReports) != count($emptyReports) && count($emptyReports)) {
                    echo "removing empty reports...";
                    foreach ($emptyReports as $rep) {
                        $em->remove($rep);

                    }
                    echo "done.";

                } else {
                    echo "no action taken";
                }
                echo "\n";

            }
        }

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
