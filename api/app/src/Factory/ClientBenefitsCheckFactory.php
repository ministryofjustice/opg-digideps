<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\MoneyReceivedOnClientsBehalfInterface;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class ClientBenefitsCheckFactory
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @throws \Exception
     */
    public function createFromFormData(array $formData, ?ClientBenefitsCheckInterface $existingEntity = null): ClientBenefitsCheck
    {
        $clientBenefitsCheck = $this->hydrateClientBenefitsCheck($formData, $existingEntity);
        $moneyTypes = $this->hydrateMoneyReceivedOnClientsBehalf($formData, $clientBenefitsCheck);

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
    private function removeMoneysIfUserChangesMind(array $formData, ClientBenefitsCheckInterface $clientBenefitsCheck): void
    {
        if (
            isset($formData['do_others_receive_money_on_clients_behalf'])
            && 'yes' !== $formData['do_others_receive_money_on_clients_behalf']
            && !empty($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf())
        ) {
            foreach ($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf() as $moneyType) {
                $this->em->remove($moneyType);
            }

            $this->em->flush();

            $clientBenefitsCheck->emptyTypeOfMoneyReceivedOnClientsBehalf();
        }
    }

    /**
     * @throws \Exception
     */
    private function hydrateClientBenefitsCheck(array $formData, ?ClientBenefitsCheckInterface $existingEntity): ClientBenefitsCheck
    {
        $report = $this->reportRepository->find($formData['report_id']);

        $dateLastChecked = isset($formData['date_last_checked_entitlement']) ? new \DateTime($formData['date_last_checked_entitlement']) : null;

        $clientBenefitsCheck = $existingEntity ?: new ClientBenefitsCheck(Uuid::uuid4());

        return $clientBenefitsCheck
            ->setReport($report)
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($formData['never_checked_explanation'] ?? null)
            ->setDontKnowMoneyExplanation($formData['dont_know_money_explanation'] ?? null)
            ->setWhenLastCheckedEntitlement($formData['when_last_checked_entitlement'] ?? null)
            ->setDoOthersReceiveMoneyOnClientsBehalf($formData['do_others_receive_money_on_clients_behalf'] ?? null);
    }

    private function hydrateMoneyReceivedOnClientsBehalf(array $formData, ClientBenefitsCheckInterface $clientBenefitsCheck): array
    {
        $moneyTypes = [];

        if (isset($formData['types_of_money_received_on_clients_behalf']) && is_array($formData['types_of_money_received_on_clients_behalf'])) {
            foreach ($formData['types_of_money_received_on_clients_behalf'] as $moneyTypeData) {
                if (is_null($moneyTypeData['id'])) {
                    $moneyType = new MoneyReceivedOnClientsBehalf();
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
                }

                $moneyType
                    ->setMoneyType($moneyTypeData['money_type'])
                    ->setWhoReceivedMoney($moneyTypeData['who_received_money'])
                    ->setAmount($moneyTypeData['amount']);

                $moneyTypes[] = $moneyType;
            }
        }

        return $moneyTypes;
    }
}
