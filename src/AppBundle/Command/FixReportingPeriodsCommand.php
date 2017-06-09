<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\FixDataService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add data that wasn't added with listeners
 * Firstly wrote when data wasn't added with temporary 103 user on staging
 *
 * @codeCoverageIgnore
 */
class FixReportingPeriodsCommand extends AddSingleUserCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:fix-data')
            ->setDescription('fix report period for PA clients reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reports = $this->reportRepo->findAll();

        /** @var Report $report */
        foreach ($reports as $report) {
            try {

                $startDate = $this->getStartDate();
                if ($report->has106Flag()) {
                    $report->setStartDate(
                        $this->getStartDate()->add(new \DateInterval('P56D'))
                    );
                    $report->setEndDate(
                        $this->getEndDate()->add(new \DateInterval('P56D'))
                    );
                    $this->messages[] = "Report {$report->getId()}: start date updated from " .
                        $startDate->format('d-M-Y') . " to " .
                        $this->getStartDate()->format('d-M-Y');

                    $this->em->flush();
                }
            } catch (\Exception $e) {
                $this->messages[] = "Report {$report->getId()}: 
                ERROR - could not be processed with start date of " .
                        $startDate->format('d-M-Y') . " to " .
                        $this->getStartDate()->format('d-M-Y') .
                    "Exception: " . $e->getMessage();
            }
        }

        return $this;
    }
}
