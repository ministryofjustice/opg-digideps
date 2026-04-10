<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Backend\Entity\Report\MoneyReceivedOnClientsBehalf;
use OPG\Digideps\Backend\TestHelpers\ClientTestHelper;
use OPG\Digideps\Backend\TestHelpers\ReportTestHelper;

class MoneyReceivedOnOthersBehalfControllerTest extends AbstractTestController
{
    private static ?string $tokenAdmin = null;
    private static ?string $tokenDeputy = null;
    private static ?string $tokenProf = null;
    private static ?string $tokenPa = null;

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

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testDeleteHasSuitablePermissionsAllowed(): void
    {
        foreach ([self::$tokenDeputy, self::$tokenPa, self::$tokenProf] as $deputyToken) {
            $report = $this->prepareReport();

            $reportUrl = sprintf(
                '/report/money-type/delete/%s',
                $report->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId()
            );


            $this->assertEndpointAllowedFor('DELETE', $reportUrl, $deputyToken);
        }
    }

    public function testDeleteHasSuitablePermissionsNotAllowed(): void
    {
        $report = $this->prepareReport();

        $reportUrl = sprintf(
            '/report/money-type/delete/%s',
            $report->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId()
        );

        $this->assertEndpointNotAllowedFor('DELETE', $reportUrl, self::$tokenAdmin);
    }

    private function prepareReport(): Report
    {
        $em = static::getContainer()->get('em');
        $reportTestHelper = ReportTestHelper::create();

        $client = (ClientTestHelper::create())->generateClient($em);

        $report = $reportTestHelper->generateReport($em);
        $report->setClient($client);

        $typeOfMoney = new MoneyReceivedOnClientsBehalf();
        $clientBenefitsCheck = new ClientBenefitsCheck();

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

        $em->persist($client);
        $em->persist($report);
        $em->flush();

        return $report;
    }
}
