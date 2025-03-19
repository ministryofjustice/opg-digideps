<?php

namespace App\Service\RestHandler\Report;

use App\Entity\Report\Report;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;

class PaFeesExpensesReportUpdateHandler implements ReportUpdateHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function handle(Report $report, array $data)
    {
        $this->initialiseFees($report, $data);

        $report->updateSectionsStatusCache([Report::SECTION_PA_DEPUTY_EXPENSES]);
    }

    /**
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
