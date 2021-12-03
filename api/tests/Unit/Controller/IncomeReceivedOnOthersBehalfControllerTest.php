<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Ndr\IncomeReceivedOnClientsBehalf as NdrIncomeReceivedOnClientsBehalf;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
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
            $report = $this->prepareReport('report', true);
            $ndr = $this->prepareReport('ndr', true);

            $reportUrl = sprintf(
                '/report/income-type/delete/%s',
                $report->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf()->first()->getId()
            );

            $ndrUrl = sprintf(
                '/ndr/income-type/delete/%s',
                $ndr->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf()->first()->getId()
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
            '/report/income-type/delete/%s',
            $report->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf()->first()->getId()
        );

        $ndrUrl = sprintf(
            '/ndr/income-type/delete/%s',
            $ndr->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf()->first()->getId()
        );

        $this->assertEndpointNotAllowedFor('DELETE', $reportUrl, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('DELETE', $ndrUrl, self::$tokenAdmin);
    }

    private function prepareReport(string $reportOrNdr, bool $withClientBenefitsCheck = false)
    {
        $em = self::$container->get('em');
        $reportTestHelper = new ReportTestHelper();

        $user = (new UserTestHelper())->createAndPersistUser($em);
        $client = (new ClientTestHelper())->generateClient($em);

        $report = 'ndr' === $reportOrNdr ? $reportTestHelper->generateNdr($em, $user, $client) : $reportTestHelper->generateReport($em);
        $report->setClient($client);

        if ($withClientBenefitsCheck) {
            $typeOfIncome = 'ndr' === $reportOrNdr ? new NdrIncomeReceivedOnClientsBehalf() : new IncomeReceivedOnClientsBehalf();
            $clientBenefitsCheck = 'ndr' === $reportOrNdr ? new NdrClientBenefitsCheck() : new ClientBenefitsCheck();

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
