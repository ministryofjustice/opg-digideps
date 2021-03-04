<?php

namespace App\Service\RestHandler\Report;

use App\Entity\Report\Report;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;

class PaFeesExpensesReportUpdateHandler implements ReportUpdateHandlerInterface
{

    /** @var EntityManagerInterface */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Report $report, array $data)
    {
        $this->initialiseFees($report, $data);

        $report->updateSectionsStatusCache([Report::SECTION_PA_DEPUTY_EXPENSES]);
    }

    /**
     * @param Report $report
     * @param array $data
     * @return $this
     */
    private function initialiseFees(Report $report, array $data)
    {
        // reason_for_no_fees must be null if user has answered 'yes' to having fees so initialise them
        if (array_key_exists('reason_for_no_fees', $data) && is_null($data['reason_for_no_fees'])) {
            /** @var ReportRepository $repo */
            $repo = $this->em->getRepository(Report::class);
            $repo->addFeesToReportIfMissing($report);
        }

        return $this;
    }
}
