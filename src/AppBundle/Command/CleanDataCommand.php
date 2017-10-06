<?php

namespace AppBundle\Command;

use AppBundle\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add data that wasn't added with listeners
 * Firstly wrote when data wasn't added with temporary 103 user on staging
 *
 * @codeCoverageIgnore
 */
class CleanDataCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('digideps:clean-data')
            ->setDescription('delete unassigned and duplicate reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rs = $this->getContainer()->get('opg_digideps.report_service');
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityManager */

        // find data to remove
        $offset = 0;
        $limit = 50;
        $clientsToRemove = [];
        $reportsToRemove = [];
        do {
            $output->write('.');
            $clients = $em->getRepository(Client::class)->findBy([], ['id'=>'ASC'], $limit, $offset);
            foreach($clients as $c) {

                /**
                 * delete client without users. Recursively (so reports will be deleted too)
                 */
                if (count($c->getUsers()) === 0) {
                    $output->writeln("client ".$c->getId()." and related reports flagged for deletion (reason: no users)");
                    $clientsToRemove[] = $c;
                } else {
                    /**
                     * cleanup reports
                     */
                    $unsubmittedReports = $c->getUnsubmittedReports();
                    if (count($unsubmittedReports) > 1) {
                        if ($deleteableReports = $rs->findDeleteableReports($unsubmittedReports)) {
                            foreach($deleteableReports as $deleteableReport) {
                                $output->writeln("report ".$deleteableReport->getId()." flagged for deletion (reason: duplicate)");
                                $reportsToRemove[] = $deleteableReport;
                            }
                        }
                    }
                }


            }
            $offset += $limit;
        } while(!empty($clients));

        $output->write("Performing deletion...");
        // remove !
        foreach($clientsToRemove as $c) {
            $em->remove($c);
            $em->flush($c);
        }
        foreach($reportsToRemove as $r) {
            $em->remove($r);
            $em->flush($r);
        }

        $output->writeln("Done");
    }

}
