<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\MoneyReceivedOnClientsBehalfInterface;
use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf as NdrMoneyReceivedOnClientsBehalf;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
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
        $moneyTypes = $this->hydrateMoneyReceivedOnClientsBehalf($reportOrNdr, $formData, $clientBenefitsCheck);

        if (!empty($moneyTypes)) {
            foreach ($moneyTypes as $moneyType) {
                $clientBenefitsCheck->addTypeOfMoneyReceivedOnClientsBehalf($moneyType);
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
            !empty($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf())) {
            foreach ($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf() as $incomeType) {
                $this->em->remove($incomeType);
            }

            $this->em->flush();

            $clientBenefitsCheck->emptyTypeOfMoneyReceivedOnClientsBehalf();
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

    private function hydrateMoneyReceivedOnClientsBehalf(
        string $reportOrNdr,
        array $formData,
        ClientBenefitsCheckInterface $clientBenefitsCheck
    ) {
        $moneyTypes = [];

        if (is_array($formData['types_of_income_received_on_clients_behalf'])) {
            foreach ($formData['types_of_income_received_on_clients_behalf'] as $moneyTypeData) {
                if (is_null($moneyTypeData['id'])) {
                    $moneyType = 'report' === $reportOrNdr ? new MoneyReceivedOnClientsBehalf() :
                        new NdrMoneyReceivedOnClientsBehalf();

                    $moneyType
                        ->setMoneyType($moneyTypeData['income_type'])
                        ->setAmount($moneyTypeData['amount']);
                } else {
                    $moneyType = $clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->filter(function (MoneyReceivedOnClientsBehalfInterface $income) use ($moneyTypeData) {
                        return $income->getId()->toString() === $moneyTypeData['id'];
                    })->first();

                    if (false === $moneyType) {
                        $message = sprintf(
                            'MoneyReceivedOnClientsBehalf with id "%s" was not associated with the ClientBenefitsCheck - cannot build entity',
                            $moneyTypeData['id']
                        );

                        throw new RuntimeException($message);
                    }

                    $moneyType
                        ->setIncomeType($moneyTypeData['income_type'])
                        ->setAmount($moneyTypeData['amount']);
                }

                $moneyTypes[] = $moneyType;
            }
        }

        return $moneyTypes;
    }
}
