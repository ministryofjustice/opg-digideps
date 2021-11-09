<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use DateTime;

class IncomeReceivedOnOthersBehalfControllerTest extends AbstractTestController
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
            $report = $this->prepareReport(true);
            $url = sprintf(
                '/income-type/delete/%s',
                $report->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf()->first()->getId()
            );

            $this->assertEndpointAllowedFor('DELETE', $url, $deputyToken);
        }
    }

    /** @test */
    public function deleteHasSuitablePermissionsNotAllowed()
    {
        $report = $this->prepareReport(true);
        $url = sprintf(
            '/income-type/delete/%s',
            $report->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf()->first()->getId()
        );

        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    private function prepareReport(bool $withClientBenefitsCheck = false)
    {
        $reportTestHelper = new ReportTestHelper();
        $em = self::$container->get('em');

        $report = $reportTestHelper->generateReport($em);
        $client = (new ClientTestHelper())->generateClient($em);

        $report->setClient($client);

        if ($withClientBenefitsCheck) {
            $typeOfIncome = new IncomeReceivedOnClientsBehalf();
            $clientBenefitsCheck = new ClientBenefitsCheck();

            $typeOfIncome->setCreated(new DateTime())
                ->setAmount(100.50)
                ->setIncomeType('Universal Credit');

            $clientBenefitsCheck->setReport($report)
                ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED)
                ->setDateLastCheckedEntitlement(new DateTime())
                ->setCreated(new DateTime())
                ->setDoOthersReceiveIncomeOnClientsBehalf('yes')
                ->addTypeOfIncomeReceivedOnClientsBehalf($typeOfIncome)
            ;

            $typeOfIncome->setClientBenefitsCheck($clientBenefitsCheck);
            $report->setClientBenefitsCheck($clientBenefitsCheck);
        }

        $em->persist($client);
        $em->persist($report);
        $em->flush();

        return $report;
    }
}
