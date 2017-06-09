<?php

namespace AppBundle\Service;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Odr\OdrRepository;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ReportRepository;
use Doctrine\ORM\EntityManager;

class FixDataService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ReportRepository
     */
    private $reportRepo;

    /**
     * @var OdrRepository
     */
    private $ndrRepo;

    /**
     * @var array
     */
    private $messages = [];


    /**
     * Total records processed
     *
     * @var int
     */
    private $totalProcessed = 0;

    /**
     * FixDataService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->reportRepo = $this->em->getRepository(Report::class);
        $this->ndrRepo = $this->em->getRepository(Odr::class);
    }

    public function fixReports()
    {
        $reports = $this->reportRepo->findAll();

        foreach ($reports as $entity) {
            $debtsAdded = $this->reportRepo->addDebtsToReportIfMissing($entity);
            if ($debtsAdded) {
                $this->messages[] = "Report {$entity->getId()}: added $debtsAdded debts";
            }
            $feesAdded = $this->reportRepo->addFeesToReportIfMissing($entity);
            if ($feesAdded) {
                $this->messages[] = "Report {$entity->getId()}: added $feesAdded fees";
            }
            $shortMoneyCatsAdded = $this->reportRepo->addMoneyShortCategoriesIfMissing($entity);
            if ($shortMoneyCatsAdded) {
                $this->messages[] = "Report {$entity->getId()}: $shortMoneyCatsAdded money short cats added";
            }
        }

        $this->em->flush();

        return $this;
    }

    public function fixNdrs()
    {
        $ndrs = $this->ndrRepo->findAll();

        foreach ($ndrs as $entity) {
            $debtsAdded = $this->ndrRepo->addDebtsToOdrIfMissing($entity);
            if ($debtsAdded) {
                $this->messages[] = "Odr {$entity->getId()}: added $debtsAdded debts";
            }
            $incomeBenefitsAdded = $this->ndrRepo->addIncomeBenefitsToOdrIfMissing($entity);
            if ($incomeBenefitsAdded) {
                $this->messages[] = "Odr {$entity->getId()}: $incomeBenefitsAdded income benefits added";
            }
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Fixes the reporting dates by pushing the start and end dates forward by
     * a period of 56 days.
     */
    public function fixPaReportingPeriods()
    {
        $reports = $this->reportRepo->findAll();
        $this->totalProcessed = 0;
        /** @var Report $report */
        foreach ($reports as $report) {
            try {
                if ($report->has106Flag()) {
                    $oldPeriod = $report->getStartDate()->format('d-M-Y') . '-->' .
                        $report->getEndDate()->format('d-M-Y');

                    $report->setStartDate($report->getStartDate()->add(new \DateInterval('P56D')));
                    $report->setEndDate($report->getEndDate()->add(new \DateInterval('P56D')));

                    $this->messages[] = "Report {$report->getId()}: Reporting period updated FROM " .
                        $oldPeriod . ' TO ' .
                        $report->getStartDate()->format('d-M-Y') . ' --> ' .
                        $report->getEndDate()->format('d-M-Y');

                } else {
                    $this->messages[] = "Report {$report->getId()}: Skipping... (not a pa client report)";
                    $this->totalProcessed++;
                }

            } catch (\Exception $e) {
                $this->messages[] = "Report {$report->getId()}: 
                ERROR - could not be processed with start date of " .
                    $report->getStartDate()->format('d-M-Y') . " to " .
                    $report->getStartDate()->format('d-M-Y') .
                    "Exception: " . $e->getMessage();
            }
        }

        $this->em->flush();

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return int
     */
    public function getTotalProcessed()
    {
        return $this->totalProcessed;
    }

}

