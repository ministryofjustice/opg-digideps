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
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class ClientBenefitsCheckFactory
{
    private ReportRepository $reportRepository;
    private NdrRepository $ndrRepository;
    private EntityManagerInterface $em;

    public function __construct(ReportRepository $reportRepository, NdrRepository $ndrRepository, EntityManagerInterface $em)
    {
        $this->reportRepository = $reportRepository;
        $this->ndrRepository = $ndrRepository;
        $this->em = $em;
    }

    public function createFromFormData(array $formData, string $reportOrNdr, ?ClientBenefitsCheckInterface $existingEntity = null)
    {
        $clientBenefitsCheck = $this->hydrateClientBenefitsCheck($reportOrNdr, $formData, $existingEntity);
        $incomeTypes = $this->hydrateIncomeReceivedOnClientsBehalf($reportOrNdr, $formData, $clientBenefitsCheck);

        if (!empty($incomeTypes)) {
            foreach ($incomeTypes as $incomeType) {
                $clientBenefitsCheck->addTypeOfIncomeReceivedOnClientsBehalf($incomeType);
            }
        }

        $this->removeIncomesIfUserChangesMind($formData, $clientBenefitsCheck);

        return $clientBenefitsCheck;
    }

    /**
     * If a user has entered income types but then changes the answer to the question on if others receive
     * income on clients' behalf we should remove the income details provided as they are no longer relevant.
     */
    private function removeIncomesIfUserChangesMind(array $formData, ClientBenefitsCheckInterface $clientBenefitsCheck)
    {
        if ('yes' !== $formData['do_others_receive_income_on_clients_behalf'] &&
            !empty($clientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf())) {
            foreach ($clientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf() as $incomeType) {
                $this->em->remove($incomeType);
            }

            $this->em->flush();

            $clientBenefitsCheck->emptyTypeOfIncomeReceivedOnClientsBehalf();
        }
    }

    /**
     * @return NdrClientBenefitsCheck|ClientBenefitsCheck
     *
     * @throws \Exception
     */
    private function hydrateClientBenefitsCheck(string $reportOrNdr, array $formData, ?ClientBenefitsCheckInterface $existingEntity)
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

        return $clientBenefitsCheck
            ->setReport($report)
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($formData['never_checked_explanation'])
            ->setDontKnowIncomeExplanation($formData['dont_know_income_explanation'])
            ->setWhenLastCheckedEntitlement($formData['when_last_checked_entitlement'])
            ->setDoOthersReceiveIncomeOnClientsBehalf($formData['do_others_receive_income_on_clients_behalf']);
    }

    private function hydrateIncomeReceivedOnClientsBehalf(
        string $reportOrNdr,
        array $formData,
        ClientBenefitsCheckInterface $clientBenefitsCheck
    ) {
        $incomeTypes = [];

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

                    if (false === $incomeType) {
                        $message = sprintf(
                            'IncomeReceivedOnClientsBehalf with id "%s" was not associated with the ClientBenefitsCheck - cannot build entity',
                            $incomeTypeData['id']
                        );

                        throw new RuntimeException($message);
                    }

                    $incomeType
                        ->setIncomeType($incomeTypeData['income_type'])
                        ->setAmount($incomeTypeData['amount']);
                }

                $incomeTypes[] = $incomeType;
            }
        }

        return $incomeTypes;
    }
}
