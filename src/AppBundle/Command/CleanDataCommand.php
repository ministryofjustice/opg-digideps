<?php

namespace AppBundle\Command;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\FixDataService;
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

        // clean up duplicates reports
        $deleted = 0;
        $total = 0;
        $offset = 0;
        $limit = 250;
        do {
            $clients = $em->getRepository(Client::class)->findBy([], ['id'=>'ASC'], $limit, $offset);
            foreach($clients as $c) {

                /**
                 * delete client without users. Recursively (so reports will be deleted too)
                 */
                if (count($c->getUsers()) === 0) {
                    $em->remove($c);
                    $em->flush($c);
                    $output->writeln("deleted client ".$c->getId()." (reason: no users)");
                }

                /**
                 * cleanup reports
                 */
                $unsubmittedReports = $c->getUnsubmittedReports();
                $total += (count($unsubmittedReports) - 1);
                if (count($unsubmittedReports) > 1) {
                    if ($deleteableReports = $rs->findDeleteableReports($unsubmittedReports)) {
                        foreach($deleteableReports as $deleteableReport) {
                            $em->remove($deleteableReport);
                            $em->flush($deleteableReport);
                            $output->writeln("deleted ".$deleteableReport->getId()." (reason: duplicate)");
                            $deleted++;
                        }
                    }
                }
            }
            $offset += $limit;
        } while(!empty($clients));

        $output->writeln("deleted $deleted / $total ");
    }

}
