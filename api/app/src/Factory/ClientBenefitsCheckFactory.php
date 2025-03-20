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
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class ClientBenefitsCheckFactory
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly NdrRepository $ndrRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function createFromFormData(array $formData, string $reportOrNdr, ClientBenefitsCheckInterface $existingEntity = null)
    {
        $clientBenefitsCheck = $this->hydrateClientBenefitsCheck($reportOrNdr, $formData, $existingEntity);
        $moneyTypes = $this->hydrateMoneyReceivedOnClientsBehalf($reportOrNdr, $formData, $clientBenefitsCheck);

        if (!empty($moneyTypes)) {
            foreach ($moneyTypes as $moneyType) {
                $clientBenefitsCheck->addTypeOfMoneyReceivedOnClientsBehalf($moneyType);
            }
        }

        $this->removeMoneysIfUserChangesMind($formData, $clientBenefitsCheck);

        return $clientBenefitsCheck;
    }

    /**
     * If a user has entered money types but then changes the answer to the question on if others receive
     * money on clients' behalf we should remove the money details provided as they are no longer relevant.
     */
    private function removeMoneysIfUserChangesMind(array $formData, ClientBenefitsCheckInterface $clientBenefitsCheck)
    {
        if (isset($formData['do_others_receive_money_on_clients_behalf'])
            && 'yes' !== $formData['do_others_receive_money_on_clients_behalf']
            && !empty($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf())) {
            foreach ($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf() as $moneyType) {
                $this->em->remove($moneyType);
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
        $report = '';

        if ('ndr' === $reportOrNdr) {
            $report = $this->ndrRepository->find($formData['ndr_id']);
        }

        if ('report' === $reportOrNdr) {
            $report = $this->reportRepository->find($formData['report_id']);
        }

        $dateLastChecked = isset($formData['date_last_checked_entitlement']) ? new \DateTime($formData['date_last_checked_entitlement']) : null;

        if ('report' === $reportOrNdr) {
            $clientBenefitsCheck = $existingEntity ?: new ClientBenefitsCheck(Uuid::uuid4());
        } else {
            $clientBenefitsCheck = $existingEntity ?: new NdrClientBenefitsCheck(Uuid::uuid4());
        }

        return $clientBenefitsCheck
            ->setReport($report)
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($formData['never_checked_explanation'] ?? null)
            ->setDontKnowMoneyExplanation($formData['dont_know_money_explanation'] ?? null)
            ->setWhenLastCheckedEntitlement($formData['when_last_checked_entitlement'] ?? null)
            ->setDoOthersReceiveMoneyOnClientsBehalf($formData['do_others_receive_money_on_clients_behalf'] ?? null);
    }

    private function hydrateMoneyReceivedOnClientsBehalf(
        string $reportOrNdr,
        array $formData,
        ClientBenefitsCheckInterface $clientBenefitsCheck
    ) {
        $moneyTypes = [];

        if (isset($formData['types_of_money_received_on_clients_behalf']) && is_array($formData['types_of_money_received_on_clients_behalf'])) {
            foreach ($formData['types_of_money_received_on_clients_behalf'] as $moneyTypeData) {
                if (is_null($moneyTypeData['id'])) {
                    $moneyType = 'report' === $reportOrNdr ? new MoneyReceivedOnClientsBehalf() :
                        new NdrMoneyReceivedOnClientsBehalf();

                    $moneyType
                        ->setMoneyType($moneyTypeData['money_type'])
                        ->setWhoReceivedMoney($moneyTypeData['who_received_money'])
                        ->setAmount($moneyTypeData['amount']);
                } else {
                    $moneyType = $clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->filter(function (MoneyReceivedOnClientsBehalfInterface $money) use ($moneyTypeData) {
                        return $money->getId()->toString() === $moneyTypeData['id'];
                    })->first();

                    if (false === $moneyType) {
                        $message = sprintf(
                            'MoneyReceivedOnClientsBehalf with id "%s" was not associated with the ClientBenefitsCheck - cannot build entity',
                            $moneyTypeData['id']
                        );

                        throw new \RuntimeException($message);
                    }

                    $moneyType
                        ->setMoneyType($moneyTypeData['money_type'])
                        ->setWhoReceivedMoney($moneyTypeData['who_received_money'])
                        ->setAmount($moneyTypeData['amount']);
                }

                $moneyTypes[] = $moneyType;
            }
        }

        return $moneyTypes;
    }
}
