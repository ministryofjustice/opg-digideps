<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf as NdrMoneyReceivedOnClientsBehalf;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;

class MoneyReceivedOnOthersBehalfControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private static $tokenDeputy;
    private static $tokenProf;
    private static $tokenPa;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenProf = $this->loginAsProf();
            self::$tokenPa = $this->loginAsPa();
        }
    }

    /** @test */
    public function deleteHasSuitablePermissionsAllowed()
    {
        $deputyTokens = [self::$tokenDeputy, self::$tokenPa, self::$tokenProf];

        foreach ($deputyTokens as $deputyToken) {
            $report = $this->prepareReport('report', true);
            $ndr = $this->prepareReport('ndr', true);

            $reportUrl = sprintf(
                '/report/money-type/delete/%s',
                $report->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId()
            );

            $ndrUrl = sprintf(
                '/ndr/money-type/delete/%s',
                $ndr->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId()
            );

            $this->assertEndpointAllowedFor('DELETE', $reportUrl, $deputyToken);
            $this->assertEndpointAllowedFor('DELETE', $ndrUrl, $deputyToken);
        }
    }

    /** @test */
    public function deleteHasSuitablePermissionsNotAllowed()
    {
        $report = $this->prepareReport('report', true);
        $ndr = $this->prepareReport('ndr', true);

        $reportUrl = sprintf(
            '/report/money-type/delete/%s',
            $report->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId()
        );

        $ndrUrl = sprintf(
            '/ndr/money-type/delete/%s',
            $ndr->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId()
        );

        $this->assertEndpointNotAllowedFor('DELETE', $reportUrl, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('DELETE', $ndrUrl, self::$tokenAdmin);
    }

    private function prepareReport(string $reportOrNdr, bool $withClientBenefitsCheck = false)
    {
        $em = static::getContainer()->get('em');
        $reportTestHelper = new ReportTestHelper();

        $user = (new UserTestHelper())->createAndPersistUser($em);
        $client = (new ClientTestHelper())->generateClient($em);

        $report = 'ndr' === $reportOrNdr ? $reportTestHelper->generateNdr($em, $user, $client) : $reportTestHelper->generateReport($em);
        $report->setClient($client);

        if ($withClientBenefitsCheck) {
            $typeOfMoney = 'ndr' === $reportOrNdr ? new NdrMoneyReceivedOnClientsBehalf() : new MoneyReceivedOnClientsBehalf();
            $clientBenefitsCheck = 'ndr' === $reportOrNdr ? new NdrClientBenefitsCheck() : new ClientBenefitsCheck();

            $typeOfMoney
                ->setCreated(new \DateTime())
                ->setAmount(100.50)
                ->setMoneyType('Universal Credit')
                ->setWhoReceivedMoney('Some org');

            $clientBenefitsCheck->setReport($report)
                ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED)
                ->setDateLastCheckedEntitlement(new \DateTime())
                ->setCreated(new \DateTime())
                ->setDoOthersReceiveMoneyOnClientsBehalf('yes')
                ->addTypeOfMoneyReceivedOnClientsBehalf($typeOfMoney)
            ;

            $typeOfMoney->setClientBenefitsCheck($clientBenefitsCheck);
            $report->setClientBenefitsCheck($clientBenefitsCheck);
        }

        $em->persist($client);
        $em->persist($report);
        $em->flush();

        return $report;
    }
}
