<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Report\ClientBenefitsCheck;
use App\Repository\ReportRepository;
use DateTime;
use Ramsey\Uuid\Uuid;

class ClientBenefitsCheckFactory
{
    private ReportRepository $repository;

    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createFromFormData(array $formData)
    {
        $report = $this->repository->find($formData['report_id']);
        $dateLastChecked = isset($formData['date_last_checked_entitlement']) ? new DateTime($formData['date_last_checked_entitlement']) : null;

        $clientBenefitsCheck = (new ClientBenefitsCheck(Uuid::uuid4()))
            ->setReport($report)
            ->setCreated(new DateTime())
            ->setWhenLastCheckedEntitlement($formData['when_last_checked_entitlement'])
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($formData['never_checked_explanation']);

        foreach ($formData['types_of_income_received_on_clients_behalf'] as $incomeType) {
            $clientBenefitsCheck->addTypeOfIncomeReceivedOnClientsBehalf($incomeType->setClientBenefitsCheck($clientBenefitsCheck));
        }

        return $clientBenefitsCheck;
    }
}
