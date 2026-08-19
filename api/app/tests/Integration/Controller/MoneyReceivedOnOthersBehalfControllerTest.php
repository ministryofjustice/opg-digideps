<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Backend\Entity\Report\MoneyReceivedOnClientsBehalf;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\Deputy\DeputyType;

class MoneyReceivedOnOthersBehalfControllerTest extends AbstractTestController
{
    public function testDeleteHasSuitablePermissionsAllowed(): void
    {
        foreach (DeputyType::cases() as $deputyType) {
            [$report, $email] = $this->prepareReport($deputyType);

            $reportUrl = sprintf(
                '/report/money-type/delete/%s',
                $report->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId()
            );


            $this->assertEndpointAllowedFor('DELETE', $reportUrl, $this->loginAsDeputy($email));
        }
    }

    public function testDeleteHasSuitablePermissionsNotAllowed(): void
    {
        [$report] = $this->prepareReport(DeputyType::LAY);

        $reportUrl = sprintf(
            '/report/money-type/delete/%s',
            $report->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId()
        );

        $this->assertEndpointNotAllowedFor('DELETE', $reportUrl, $this->loginAsAdmin());
    }

    /**
     * @return array{Report, string}
     */
    private function prepareReport(DeputyType $deputyType): array
    {
        ['persons' => ['users' => ['deputy' => $user]], 'orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(new DeputySet(new DeputyDescriptor('deputy', $deputyType)))), flush: false);

        $typeOfMoney = self::$fixtureService->persist(new MoneyReceivedOnClientsBehalf());
        $clientBenefitsCheck = self::$fixtureService->persist(new ClientBenefitsCheck());

        $typeOfMoney->setCreated(new \DateTime())
            ->setAmount(100.50)
            ->setMoneyType('Universal Credit')
            ->setWhoReceivedMoney('Some org');

        $clientBenefitsCheck->setReport($report)
            ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED)
            ->setDateLastCheckedEntitlement(new \DateTime())
            ->setCreated(new \DateTime())
            ->setDoOthersReceiveMoneyOnClientsBehalf('yes')
            ->addTypeOfMoneyReceivedOnClientsBehalf($typeOfMoney);

        $typeOfMoney->setClientBenefitsCheck($clientBenefitsCheck);
        $report->setClientBenefitsCheck($clientBenefitsCheck);

        self::$fixtureService->flush();

        return [$report, $user->getEmail()];
    }
}
