<?php

namespace AppBundle\Command;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Update report.statusCached when empty
 *
 * @codeCoverageIgnore
 */
class ReportStatusUpdaterCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('digideps:report-status-update')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, '', 1000)
            ->setDescription('update report status when missing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getOption('limit');
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityManager */

        $chunkSize = 10;

        $output->write("Updating report status for next $limit reports: ");
        for ($i = 0, $continue = true; $i < $limit && $continue; $i += $chunkSize) {
            /* @var $reports Report[] */
            $reports = $em->getRepository(Report::class)
                ->createQueryBuilder('r')
                ->select('r')
                ->where('r.statusCached IS NULL OR r.reportStatusCached IS NULL')
                ->orderBy('r.id', 'DESC')
                ->setMaxResults($chunkSize)
                ->getQuery()
                ->getResult();
            if (empty($reports)) {
                $output->writeln("Done");
                return 0;
            }
            foreach ($reports as $report) {
                $report->setStatusCached([]);
                /* @var $report Report */
                $report->updateSectionsStatusCache($report->getAvailableSections());
            }

            if ($i % 100===0) {$output->write(" $i ");}
            $em->flush();
            $em->clear(); //release memory
        }
        $em->flush();

        $output->writeln("Done");
    }
}
