<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Backend\Entity\Report\MoneyReceivedOnClientsBehalf;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Fixture\Scenario;

class ClientBenefitsCheckControllerTest extends AbstractTestController
{
    private static string $tokenAdmin = '';
    private static string $tokenDeputy = '';
    private static string $tokenProf = '';
    private static string $tokenPa = '';
    private ?array $okayData = null;

    public function setUp(): void
    {
        parent::setUp();

        if (self::$tokenAdmin === '') {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenProf = $this->loginAsProf();
            self::$tokenPa = $this->loginAsPa();
        }

        $this->okayData = [
            'report_id' => null,
            'id' => null,
            'created' => '2021-10-20',
            'when_last_checked_entitlement' => 'haveChecked',
            'date_last_checked_entitlement' => '2020-01-01',
            'never_checked_explanation' => null,
            'do_others_receive_income_on_clients_behalf' => 'yes',
            'dont_know_income_explanation' => null,
            'types_of_income_received_on_clients_behalf' => [
                [
                    'id' => null,
                    'created' => '2021-10-20',
                    'client_benefits_check' => null,
                    'income_type' => 'Test income',
                    'amount' => 225.69,
                    'amount_dont_know' => null,
                ],
            ],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testCreateHasSuitablePermissionsAllowed(): void
    {
        $deputyTokens = [self::$tokenDeputy, self::$tokenPa, self::$tokenProf];
        $url = '/report/client-benefits-check';

        foreach ($deputyTokens as $deputyToken) {
            $report = $this->prepareReport();
            $this->okayData['report_id'] = $report->getId();

            $this->assertEndpointAllowedFor('POST', $url, $deputyToken, $this->okayData);
        }
    }

    public function testCreateHasSuitablePermissionsNotAllowed(): void
    {
        $url = '/report/client-benefits-check';

        $report = $this->prepareReport();
        $this->okayData['report_id'] = $report->getId();

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin, $this->okayData);
    }

    public function testReadHasSuitablePermissionsAllowed(): void
    {
        $deputyTokens = [self::$tokenDeputy, self::$tokenPa, self::$tokenProf];

        foreach ($deputyTokens as $deputyToken) {
            $report = $this->prepareReport(true);

            $url = sprintf('/report/client-benefits-check/%s', $report->getClientBenefitsCheck()?->getId());
            $this->assertEndpointAllowedFor('GET', $url, $deputyToken);
        }
    }

    public function testReadHasSuitablePermissionsNotAllowed(): void
    {
        $report = $this->prepareReport(true);

        $url = sprintf('/report/client-benefits-check/%s', $report->getClientBenefitsCheck()?->getId());
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testUpdateHasSuitablePermissionsAllowed(): void
    {
        $deputyTokens = [self::$tokenDeputy, self::$tokenPa, self::$tokenProf];

        foreach ($deputyTokens as $deputyToken) {
            $report = $this->prepareReport(true);
            $clientBenefitsCheck = $report->getClientBenefitsCheck();
            $this->assertNotNull($clientBenefitsCheck);
            $url = sprintf('/report/client-benefits-check/%s', $clientBenefitsCheck->getId());

            $this->okayData['report_id'] = $report->getId();
            $this->okayData['types_of_income_received_on_clients_behalf'][0]['id'] = $clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId();
            $this->okayData['types_of_income_received_on_clients_behalf'][1] = [
                'id' => null,
                'created' => '2021-10-20',
                'client_benefits_check' => null,
                'income_type' => 'Some more test income',
                'amount' => 0.78,
                'amount_dont_know' => null,
            ];

            $this->assertEndpointAllowedFor('PUT', $url, $deputyToken, $this->okayData);
        }
    }

    public function testUpdateHasSuitablePermissionsNotAllowed(): void
    {
        $report = $this->prepareReport(true);
        $clientBenefitsCheck = $report->getClientBenefitsCheck();
        $this->assertNotNull($clientBenefitsCheck);
        $url = sprintf('/report/client-benefits-check/%s', $clientBenefitsCheck->getId());

        $this->okayData['report_id'] = $report->getId();
        $this->okayData['types_of_income_received_on_clients_behalf'][0]['id'] = $clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->first()->getId();
        $this->okayData['types_of_income_received_on_clients_behalf'][1] = [
            'id' => null,
            'created' => '2021-10-20',
            'client_benefits_check' => null,
            'income_type' => 'Some more test income',
            'amount' => 0.78,
            'amount_dont_know' => null,
        ];

        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin, $this->okayData);
    }

    private function prepareReport(bool $withClientBenefitsCheck = false): Report
    {
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        if ($withClientBenefitsCheck) {
            $typeOfIncome = new MoneyReceivedOnClientsBehalf();
            $clientBenefitsCheck = new ClientBenefitsCheck();

            $typeOfIncome->setCreated(new \DateTime())
                ->setAmount(100.50)
                ->setMoneyType('Universal Credit')
                ->setWhoReceivedMoney('Some org');

            $clientBenefitsCheck->setReport($report)
                ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED)
                ->setDateLastCheckedEntitlement(new \DateTime())
                ->setCreated(new \DateTime())
                ->setDoOthersReceiveMoneyOnClientsBehalf('yes')
                ->addTypeOfMoneyReceivedOnClientsBehalf($typeOfIncome)
            ;

            $typeOfIncome->setClientBenefitsCheck($clientBenefitsCheck);
            $report->setClientBenefitsCheck($clientBenefitsCheck);
            self::$fixtureService->persist($clientBenefitsCheck);
            self::$fixtureService->persist($report);
        }

        self::$fixtureService->flush();

        return $report;
    }
}
