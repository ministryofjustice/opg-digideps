<?php

namespace App\Command;

use App\Entity\Report\Report;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Update report.sectionStatusesCached when empty.
 *
 * @codeCoverageIgnore
 */
class ReportStatusUpdaterCommand extends Command
{
    use ContainerAwareTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

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
        $em = $this->entityManager;

        $chunkSize = 10;

        $output->write("Updating report status for next $limit reports: ");
        for ($i = 0, $continue = true; $i < $limit && $continue; $i += $chunkSize) {
            /** @var Report[] $reports */
            $reports = $em->getRepository(Report::class)
                ->createQueryBuilder('r')
                ->select('r')
                ->where('r.sectionStatusesCached IS NULL OR r.reportStatusCached IS NULL')
                ->orderBy('r.id', 'DESC')
                ->setMaxResults($chunkSize)
                ->getQuery()
                ->getResult();
            if (empty($reports)) {
                $output->writeln('Done');

                return 0;
            }
            foreach ($reports as $report) {
                $report->setSectionStatusesCached([]);
                $report->updateSectionsStatusCache($report->getAvailableSections());
            }

            if (0 === $i % 100) {
                $output->write(" $i ");
            }
            $em->flush();
            $em->clear(); // release memory
        }
        $em->flush();

        $output->writeln('Done');
    }
}
