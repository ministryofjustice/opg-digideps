<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
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

    public function createFromFormData(array $formData, ?ClientBenefitsCheck $existingEntity = null)
    {
        $report = $this->repository->find($formData['report_id']);
        $dateLastChecked = isset($formData['date_last_checked_entitlement']) ? new DateTime($formData['date_last_checked_entitlement']) : null;

        $clientBenefitsCheck = $existingEntity ?: new ClientBenefitsCheck(Uuid::uuid4());

        $clientBenefitsCheck
            ->setReport($report)
            ->setCreated(new DateTime())
            ->setWhenLastCheckedEntitlement($formData['when_last_checked_entitlement'])
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($formData['never_checked_explanation'])
            ->setDoOthersReceiveIncomeOnClientsBehalf($formData['do_others_receive_income_on_clients_behalf'])
            ->setDontKnowIncomeExplanation($formData['dont_know_income_explanation']);

        if (is_array($formData['types_of_income_received_on_clients_behalf'])) {
            foreach ($formData['types_of_income_received_on_clients_behalf'] as $incomeTypeData) {
                if (is_null($incomeTypeData['id'])) {
                    $incomeType = (new IncomeReceivedOnClientsBehalf())
                        ->setIncomeType($incomeTypeData['income_type'])
                        ->setAmount($incomeTypeData['amount']);
                } else {
                    $incomeType = $clientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf()->filter(function (IncomeReceivedOnClientsBehalf $income) use ($incomeTypeData) {
                        return $income->getId()->toString() === $incomeTypeData['id'];
                    })->first();

                    $incomeType
                        ->setIncomeType($incomeTypeData['income_type'])
                        ->setAmount($incomeTypeData['amount']);
                }

                $clientBenefitsCheck->addTypeOfIncomeReceivedOnClientsBehalf($incomeType);
            }
        }

        return $clientBenefitsCheck;
    }
}
