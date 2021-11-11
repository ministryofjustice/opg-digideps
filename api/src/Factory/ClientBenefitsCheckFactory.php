<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\IncomeReceivedOnClientsBehalfInterface;
use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Ndr\IncomeReceivedOnClientsBehalf as NdrIncomeReceivedOnClientsBehalf;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\Repository\NdrRepository;
use App\Repository\ReportRepository;
use DateTime;
use Ramsey\Uuid\Uuid;

class ClientBenefitsCheckFactory
{
    private ReportRepository $reportRepository;
    private NdrRepository $ndrRepository;

    public function __construct(ReportRepository $reportRepository, NdrRepository $ndrRepository)
    {
        $this->reportRepository = $reportRepository;
        $this->ndrRepository = $ndrRepository;
    }

    public function createFromFormData(array $formData, string $reportOrNdr, ?ClientBenefitsCheckInterface $existingEntity = null)
    {
        if ('ndr' === $reportOrNdr) {
            $report = $this->ndrRepository->find($formData['ndr_id']);
        }

        if ('report' === $reportOrNdr) {
            $report = $this->reportRepository->find($formData['report_id']);
        }

        $dateLastChecked = isset($formData['date_last_checked_entitlement']) ? new DateTime($formData['date_last_checked_entitlement']) : null;

        if ('report' === $reportOrNdr) {
            $clientBenefitsCheck = $existingEntity ?: new ClientBenefitsCheck(Uuid::uuid4());
        } else {
            $clientBenefitsCheck = $existingEntity ?: new NdrClientBenefitsCheck(Uuid::uuid4());
        }

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
                    $incomeType = 'report' === $reportOrNdr ? new IncomeReceivedOnClientsBehalf() :
                        new NdrIncomeReceivedOnClientsBehalf();

                    $incomeType
                        ->setIncomeType($incomeTypeData['income_type'])
                        ->setAmount($incomeTypeData['amount']);
                } else {
                    $incomeType = $clientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf()->filter(function (IncomeReceivedOnClientsBehalfInterface $income) use ($incomeTypeData) {
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
