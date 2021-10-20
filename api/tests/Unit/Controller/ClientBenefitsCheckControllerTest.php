<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use DateTime;

class ClientBenefitsCheckControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private static $tokenDeputy;
    private static $tokenProf;
    private static $tokenPa;
    private $okayData;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
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

    public function testCreateHasSuitablePermissionsAllowed()
    {
        $deputyTokens = [self::$tokenDeputy, self::$tokenPa, self::$tokenProf];
        $url = '/report/client-benefits-check';

        foreach ($deputyTokens as $deputyToken) {
            $report = $this->prepareReport();
            $this->okayData['report_id'] = $report->getId();

            $this->assertEndpointAllowedFor('POST', $url, $deputyToken, $this->okayData);
        }
    }

    public function testCreateHasSuitablePermissionsNotAllowed()
    {
        $url = '/report/client-benefits-check';

        $report = $this->prepareReport();
        $this->okayData['report_id'] = $report->getId();

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin, $this->okayData);
    }

    public function testReadHasSuitablePermissionsAllowed()
    {
        $deputyTokens = [self::$tokenDeputy, self::$tokenPa, self::$tokenProf];

        foreach ($deputyTokens as $deputyToken) {
            $report = $this->prepareReport(true);

            $url = sprintf('/report/client-benefits-check/%s', $report->getClientBenefitsCheck()->getId());
            $this->assertEndpointAllowedFor('GET', $url, $deputyToken);
        }
    }

    public function testReadHasSuitablePermissionsNotAllowed()
    {
        $report = $this->prepareReport(true);

        $url = sprintf('/report/client-benefits-check/%s', $report->getClientBenefitsCheck()->getId());
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testUpdateHasSuitablePermissionsAllowed()
    {
        $deputyTokens = [self::$tokenDeputy, self::$tokenPa, self::$tokenProf];

        foreach ($deputyTokens as $deputyToken) {
            $report = $this->prepareReport(true);
            $url = sprintf('/report/client-benefits-check/%s', $report->getClientBenefitsCheck()->getId());

            $this->okayData['report_id'] = $report->getId();
            $this->okayData['types_of_income_received_on_clients_behalf'][0]['id'] = $report->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf()->first()->getId();
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

    public function testUpdateHasSuitablePermissionsNotAllowed()
    {
        $report = $this->prepareReport(true);
        $url = sprintf('/report/client-benefits-check/%s', $report->getClientBenefitsCheck()->getId());

        $this->okayData['report_id'] = $report->getId();
        $this->okayData['types_of_income_received_on_clients_behalf'][0]['id'] = $report->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf()->first()->getId();
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
