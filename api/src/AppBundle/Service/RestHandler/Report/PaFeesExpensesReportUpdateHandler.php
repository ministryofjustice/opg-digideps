<?php

namespace AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ReportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class PaFeesExpensesReportUpdateHandler implements ReportUpdateHandlerInterface
{

    /** @var EntityManager */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
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

