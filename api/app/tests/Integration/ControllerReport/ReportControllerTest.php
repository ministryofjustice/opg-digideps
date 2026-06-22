<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\Report\Checklist;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\Fee;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use Tests\OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use Tests\OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use Tests\OPG\Digideps\Backend\Fixture\DeputySet;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class ReportControllerTest extends AbstractTestController
{
    private static Client $client1;
    private static Client $client2;
    private static Client $client3;
    private static CourtOrder $order3;
    private static PreRegistration $preRegistration1;
    private static PreRegistration $preRegistration3;
    private static Report $pa1Client1Report1;
    private static Report $pa2Client1Report1;
    private static Report $pa3Client1Report1;
    private static Report $report103;
    private static Report $report1;
    private static Report $report2;
    private static Report $reportEdit;
    private static User $user1;

    private static string $tokenAdmin;
    private static string $tokenDeputy;
    private static string $tokenPa;
    private static string $tokenPaAdmin;
    private static string $tokenPaTeamMember;

    public function setUp(): void
    {
        parent::setUp();

        $result = self::$fixtureService->instantiateScenario(new Scenario(
            new CourtOrderDescriptor(new DeputySet(new DeputyDescriptor('lay1')), CourtOrderReportType::OPG102, 1),
            new Scenario(new CourtOrderDescriptor(new DeputySet(new DeputyDescriptor('lay1'))))
        ));
        ['client' => self::$client1, 'persons' => ['users' => ['lay1' => self::$user1]], 'orders' => [['pfa' => ['order' => $order1, 'reports' => [self::$report1, self::$reportEdit]]], ['pfa' => ['reports' => [self::$report103]]]]] = $result;
        ['client' => self::$client3, 'orders' => [['pfa' => ['order' => self::$order3]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(new DeputySet(new DeputyDescriptor('lay1')), CourtOrderReportType::OPG102, noReports: true)), $result['persons']);
        ['client' => self::$client2, 'orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$report1->setWishToProvideDocumentation(true);

        $document = new Document(self::$report1)
            ->setFileName('test.pdf')
            ->setIsReportPdf(false);
        self::fixtures()->persist(self::$report1, $document);

        $result = self::$fixtureService->instantiateScenario(Scenario::newSimplePaScenario(reportType: CourtOrderReportType::OPG102));
        self::$fixtureService->instantiateScenario(Scenario::newSimplePaScenario(reportType: CourtOrderReportType::OPG102), $result['persons']);
        self::$fixtureService->instantiateScenario(Scenario::newSimplePaScenario(reportType: CourtOrderReportType::OPG102), $result['persons']);
        ['persons' => ['users' => ['pa1' => $pa1]], 'orders' => [['pfa' => ['reports' => [self::$pa1Client1Report1]]]]] = $result;
        ['persons' => ['users' => ['admin1' => $pa2Admin]], 'orders' => [['pfa' => ['reports' => [self::$pa2Client1Report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleAdminPaScenario(reportType: CourtOrderReportType::OPG102));
        ['persons' => ['users' => ['team1' => $pa3TeamMember]], 'orders' => [['pfa' => ['reports' => [self::$pa3Client1Report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleTeamMemberPaScenario(reportType: CourtOrderReportType::OPG102));

        self::$preRegistration1 = new PreRegistration([
            'Case' => self::$client1->getCaseNumber(),
            'ClientSurname' => self::$client1->getLastName(),
            'DeputyUid' => (string) self::$user1->getDeputyUid(),
            'DeputyFirstname' => self::$user1->getFirstname(),
            'DeputySurname' => self::$user1->getLastname(),
            'DeputyPostcode' => self::$user1->getAddressPostcode(),
            'ReportType' => $order1->getOrderReportType()->value,
            'MadeDate' => $order1->getOrderMadeDate()->format('Y-m-d'),
            'OrderType' => $order1->getOrderType()->value,
            'CoDeputy' => false,
            'Hybrid' => 'SINGLE',
        ]);

        // New registration - no old submitted reports therefore report will start from current year
        self::$preRegistration3 = new PreRegistration([
            'Case' => self::$client3->getCaseNumber(),
            'ClientSurname' => self::$client3->getLastName(),
            'DeputyUid' => (string) self::$user1->getDeputyUid(),
            'DeputyFirstname' => self::$user1->getFirstname(),
            'DeputySurname' => self::$user1->getLastname(),
            'DeputyPostcode' => self::$user1->getAddressPostcode(),
            'ReportType' => self::$order3->getOrderReportType()->value,
            'MadeDate' => self::$order3->getOrderMadeDate()->format('Y-m-d'),
            'OrderType' => self::$order3->getOrderType()->value,
            'CoDeputy' => false,
            'Hybrid' => 'SINGLE',
        ]);

        self::$fixtureService->persist(self::$preRegistration1);
        self::$fixtureService->persist(self::$preRegistration3);


        self::fixtures()->flush()->clear();

        self::$tokenAdmin = $this->loginAsAdmin();
        self::$tokenDeputy = $this->loginAsDeputy(self::$user1->getEmail());
        self::$tokenPa = $this->loginAsPa($pa1->getEmail());
        self::$tokenPaAdmin = $this->loginAsPaAdmin($pa2Admin->getEmail());
        self::$tokenPaTeamMember = $this->loginAsPaTeamMember($pa3TeamMember->getEmail());
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testAddAuth(): void
    {
        $url = '/report';
        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }

    public function testAddAcl(): void
    {
        $url = '/report';
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy, [
            'client' => ['id' => self::$client2->getId()],
        ]);
    }

    public function testAdd(): void
    {
        $url = '/report';

        // add new report
        $reportId = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'client' => ['id' => self::$client3->getId()],
            ],
        ])['data']['report'];

        self::fixtures()->clear();

        // assert creation
        $report = self::fixtures()->getReportById($reportId);

        $this->assertEquals(self::$client3->getId(), $report->getClient()->getId());

        $this->assertEquals(self::$order3->getOrderMadeDate()->format('Y-m-d'), $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals(self::$order3->getOrderMadeDate()->add(new \DateInterval('P1Y'))->sub(new \DateInterval('P1D'))->format('Y-m-d'), $report->getEndDate()->format('Y-m-d'));

        self::fixtures()->flush();
    }

    public function testGetByIdAuth(): void
    {
        $url = '/report/' . self::$report1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointAllowedFor('GET', $url, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('GET', $url, self::$tokenDeputy);
    }

    public function testGetByIdAuthPa(): void
    {
        $urlReport1 = '/report/' . self::$pa1Client1Report1->getId();
        $urlReport2 = '/report/' . self::$pa2Client1Report1->getId();
        $urlReport3 = '/report/' . self::$pa3Client1Report1->getId();

        $this->assertEndpointAllowedFor('GET', $urlReport1, self::$tokenPa);
        $this->assertEndpointAllowedFor('GET', $urlReport2, self::$tokenPaAdmin);
        $this->assertEndpointAllowedFor('GET', $urlReport3, self::$tokenPaTeamMember);

        $this->assertEndpointNotAllowedFor('GET', $urlReport2, self::$tokenPa);
        $this->assertEndpointNotAllowedFor('GET', $urlReport3, self::$tokenPaAdmin);
        $this->assertEndpointNotAllowedFor('GET', $urlReport1, self::$tokenPaTeamMember);
    }

    public function testGetByIdAcl(): void
    {
        $url2 = '/report/' . self::$report2->getId();

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetById(): void
    {
        $clientReportData = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['report', 'report-client', 'client']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data'];

        $this->assertArrayHasKey('report_seen', $clientReportData);
        $this->assertArrayNotHasKey('transactions', $clientReportData);
        $this->assertArrayNotHasKey('debts', $clientReportData);
        $this->assertArrayNotHasKey('fees', $clientReportData);
        $this->assertEquals(self::$report1->getId(), $clientReportData['id']);
        $this->assertEquals(self::$client1->getId(), $clientReportData['client']['id']);
        $this->assertEquals(true, $clientReportData['submitted']);
        $this->assertArrayHasKey('start_date', $clientReportData);
        $this->assertArrayHasKey('end_date', $clientReportData);

        // assert decisions
        $decisionData = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['decision']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data'];

        $this->assertArrayHasKey('decisions', $decisionData);

        // assert assets
        $assetsData = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['asset']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data'];

        $this->assertArrayHasKey('assets', $assetsData);

        // assert debts
        $debtsData = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['debt']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data'];

        $this->assertArrayHasKey('debts', $debtsData);

        // assert fees
        $feesData = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['fee']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data'];

        $this->assertArrayHasKey('fees', $feesData);

        // assert report-submitted-by + user info
        $submittedByData = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['report-submitted-by']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data'];

        $this->assertEquals(self::$user1->getId(), $submittedByData['submitted_by']['id']);
        $this->assertEquals(self::$user1->getEmail(), $submittedByData['submitted_by']['email']);

        // assert status
        $statusData = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['status']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data']['status'];

        foreach (
            [
            // add here the jms_serialised_version of the ReportStatus getters
            'decisions_state',
            'contacts_state',
            'visits_care_state',
            'bank_accounts_state',
            'money_transfer_state',
            'money_in_state',
            'money_out_state',
            'money_in_short_state',
            'money_out_short_state',
            'balance_state',
            'assets_state',
            'debts_state',
            'pa_fees_expenses_state',
            'actions_state',
            'other_info_state',
            'expenses_state',
            'gifts_state',
            ] as $key
        ) {
            $this->assertArrayHasKey('state', $statusData[$key]);
            $this->assertArrayHasKey('nOfRecords', $statusData[$key]);
        }

        $this->assertArrayHasKey('status', $statusData);
    }

    public function testSubmit(): void
    {
        $url = '/report/' . self::$reportEdit->getId() . '/submit';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', '/report/' . self::$report2->getId() . '/submit', self::$tokenDeputy);

        $report = self::fixtures()->clear()->getReportById(self::$reportEdit->getId());
        $this->assertNotNull($report);

        // add one document
        $document = new Document($report);
        $document->setFileName('file2.pdf')->setStorageReference('storageref1');
        self::fixtures()->persist($document)->flush();
        $this->assertEquals(false, $report->getSubmitted());

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30',
                'agreed_behalf_deputy' => 'only_deputy',
                'agreed_behalf_deputy_explanation' => 'should not be saved',
            ],
        ]);

        // assert account created with transactions
        $report = self::fixtures()->clear()->getReportById(self::$reportEdit->getId());
        $this->assertNotNull($report);

        $this->assertEquals(true, $report->getSubmitted());
        $this->assertEquals(self::$user1->getId(), $report->getSubmittedBy()?->getId());
        $this->assertEquals('only_deputy', $report->getAgreedBehalfDeputy());
        $this->assertEquals(null, $report->getAgreedBehalfDeputyExplanation());
        $this->assertEquals('2015-12-30', $report->getSubmitDate()?->format('Y-m-d'));

        // assert submission is created
        $data = $this->assertJsonRequest('GET', '/report-submission?status=pending', [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];
        $this->assertEquals(1, $data['counts']['pending']);
        $this->assertEquals('file2.pdf', $data['records'][0]['documents'][0]['file_name']);
    }

    public function testUnsubmit(): void
    {
        $urlSubmit = '/report/' . self::$reportEdit->getId() . '/submit';

        // submit
        $this->assertJsonRequest('PUT', $urlSubmit, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30',
                'agreed_behalf_deputy' => 'only_deputy',
                'agreed_behalf_deputy_explanation' => 'should not be saved',
            ],
        ]);

        $url = '/report/' . self::$reportEdit->getId() . '/unsubmit';

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);

        // assert get
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'un_submit_date' => '2018-01-01',
                'due_date' => '2019-01-01',
                'start_date' => '2019-02-01',
                'end_date' => '2019-03-01',
                'unsubmitted_sections_list' => 'decisions,contacts',
            ],
        ]);

        // both
        $q = http_build_query(['groups' => ['report']]);
        // assert both groups (quick)
        $data = $this->assertJsonRequest('GET', '/report/' . self::$reportEdit->getId() . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals('2018-01-01', $data['un_submit_date']);
        $this->assertEquals('2019-01-01', $data['due_date']);
        $this->assertEquals('2019-02-01', $data['start_date']);
        $this->assertEquals('2019-03-01', $data['end_date']);
        $this->assertEquals('decisions,contacts', $data['unsubmitted_sections_list']);
        $this->assertEquals(false, $data['submitted']);
        $this->assertNotNull($data['submit_date']);
    }

    public function testUpdateAuth(): void
    {
        $url = '/report/' . self::$report1->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testUpdateAcl(): void
    {
        $url2 = '/report/' . self::$report2->getId();

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testUpdate(): void
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId;

        self::fixtures()->getReportById($reportId)->setDueDate(new \DateTime('2016-11-30'));
        self::fixtures()->flush()->clear();

        // assert get
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'start_date' => '2016-01-01',
                'end_date' => '2016-11-30',
                'balance_mismatch_explanation' => 'bme',
            ],
        ]);

        // both
        $q = http_build_query(['groups' => ['report']]);
        // assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals('2016-01-01', $data['start_date']);
        $this->assertEquals('2016-11-30', $data['end_date']);
        $this->assertEquals('2016-12-21', $data['due_date']);

        // repeat test with new end date beyond 13th November 2019
        // assert put new end date
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'start_date' => '2019-01-01',
                'end_date' => '2019-11-30',
                'balance_mismatch_explanation' => 'bme',
            ],
        ]);
        // assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals('2019-01-01', $data['start_date']);
        $this->assertEquals('2019-11-30', $data['end_date']);
        $this->assertEquals('2019-12-21', $data['due_date']);
    }

    public function testDebts(): void
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId;

        // "yes"
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'has_debts' => 'yes',
                'debts' => [
                    ['debt_type_id' => 'care-fees', 'amount' => 1, 'more_details' => 'should not be saved'],
                    ['debt_type_id' => 'credit-cards', 'amount' => 2, 'more_details' => ''],
                    ['debt_type_id' => 'loans', 'amount' => 3, 'more_details' => ''],
                    ['debt_type_id' => 'other', 'amount' => 4, 'more_details' => 'md'],
                ],
            ],
        ]);

        $q = http_build_query(['groups' => ['debt']]);
        // assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $debt = array_shift($data['debts']);
        $this->assertEquals('care-fees', $debt['debt_type_id']);
        $this->assertEquals(1, $debt['amount']);
        $this->assertEquals('', $debt['more_details']);
        $debt = array_shift($data['debts']);
        $this->assertEquals('credit-cards', $debt['debt_type_id']);
        $this->assertEquals(2.00, $debt['amount']);
        $this->assertEquals('', $debt['more_details']);
        $debt = array_shift($data['debts']);
        $this->assertEquals('loans', $debt['debt_type_id']);
        $this->assertEquals(3.00, $debt['amount']);
        $this->assertEquals('', $debt['more_details']);
        $debt = array_shift($data['debts']);
        $this->assertEquals('other', $debt['debt_type_id']);
        $this->assertEquals(4.00, $debt['amount']);
        $this->assertEquals('md', $debt['more_details']);
        $this->assertEquals(10, $data['debts_total_amount']);
        $this->assertEquals('yes', $data['has_debts']);

        // "no"
        self::fixtures()->flush()->clear();
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'has_debts' => 'no',
                'debts' => [],
            ],
        ]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $debt = array_shift($data['debts']);
        $this->assertEquals('care-fees', $debt['debt_type_id']);
        $this->assertEquals(0, $debt['amount']);
        $this->assertEquals('', $debt['more_details']);
        $this->assertEquals(0, $data['debts_total_amount']);
        $this->assertEquals('no', $data['has_debts']);
    }

    public function testPaFeesEditResetAndTotals(): void
    {
        $reportId = self::$pa1Client1Report1->getId();
        $url = '/report/' . $reportId;

        // save 2 fees and check they are retrieved
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
            'data' => [
                'reason_for_no_fees' => null,
                'fees' => [
                    ['fee_type_id' => 'annual-management-fee', 'amount' => 1.1, 'more_details' => 'should be ignored'],
                    ['fee_type_id' => 'travel-costs', 'amount' => 1.2, 'more_details' => 'tc.md'],
                ],
            ],
        ]);

        $q = http_build_query(['groups' => ['fee']]);
        // assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];

        $this->assertCount(7, $data['fees']);
        $this->assertEquals(1.1 + 1.2, $data['fees_total']);
        $this->assertEquals('yes', $data['has_fees']);

        $row = $data['fees'][1];
        $this->assertEquals('annual-management-fee', $row['fee_type_id']);
        $this->assertEquals(1.1, $row['amount']);
        $this->assertEquals(false, $row['has_more_details']);
        $this->assertEquals(null, $row['more_details']);

        $row = $data['fees'][5];
        $this->assertEquals('travel-costs', $row['fee_type_id']);
        $this->assertEquals(1.2, $row['amount']);
        $this->assertEquals(true, $row['has_more_details']);
        $this->assertEquals('tc.md', $row['more_details']);

        // "add reason. And assert fees are reset
        self::fixtures()->flush()->clear();
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
            'data' => [
                'reason_for_no_fees' => 'rfnf',
                'fees' => [],
            ],
        ]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];

        $this->assertEquals('rfnf', $data['reason_for_no_fees']);
        $this->assertCount(count(Fee::$feeTypeIds), $data['fees']);
        $this->assertEquals(0, $data['fees_total']);
        $this->assertEquals('no', $data['has_fees']);
    }

    public function testActions(): void
    {
        $url = '/report/' . self::$report1->getId();

        // PUT
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'action_more_info' => 'yes',
                'action_more_info_details' => 'md2',
            ],
        ]);

        // GET and assert
        $q = http_build_query(['groups' => [
            'action-more-info',
        ]]);

        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals('yes', $data['action_more_info']);
        $this->assertEquals('md2', $data['action_more_info_details']);
    }

    public function testMoneyInLowAssetDoesNotExist(): void
    {
        $reportId = self::$report103->getId();

        $url = '/report/' . $reportId;
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'money_in_exists' => 'No',
            ]])['data'];

        self::$report103 = self::fixtures()->getReportById($reportId) ?? throw new \LogicException('Bad fixture setup');

        $moneyInSectionStateStatusShort = self::fixtures()->getReportFreshSectionStatus(self::$report103, Report::SECTION_MONEY_IN_SHORT)['state'];

        $this->assertEquals('No', self::$report103->getMoneyInExists());
        $this->assertEquals('incomplete', $moneyInSectionStateStatusShort);
    }

    public function testMoneyInLowAssetDoesNotExistWithReason(): void
    {
        $report = self::fixtures()->getReportById(self::$report103->getId());
        $this->assertNotNull($report);
        $reportId = $report->getId();

        $report->setMoneyInExists('No');
        self::fixtures()->persist($report)->flush();

        $url = '/report/' . $reportId;

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'reason_for_no_money_in' => 'No money in',
            ]])['data'];

        self::fixtures()->clear();
        $report = self::fixtures()->getReportById(self::$report103->getId());

        $moneyInSectionStateStatusShort = self::fixtures()->getReportFreshSectionStatus($report, Report::SECTION_MONEY_IN_SHORT)['state'];

        $this->assertEquals('No money in', $report->getReasonForNoMoneyIn());
        $this->assertEquals('done', $moneyInSectionStateStatusShort);
    }

    public function testMoneyOutHighAssetExists(): void
    {
        $reportId = self::$pa1Client1Report1->getId();

        $url = '/report/' . $reportId;
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
            'data' => [
                'money_out_exists' => 'Yes',
            ]])['data'];

        self::fixtures()->clear();
        self::$pa1Client1Report1 = self::fixtures()->getReportById($reportId) ?? throw new \LogicException('Bad fixture setup');

        $moneyOutSectionStateStatus = self::fixtures()->getReportFreshSectionStatus(self::$pa1Client1Report1, Report::SECTION_MONEY_OUT)['state'];

        $this->assertEquals('Yes', self::$pa1Client1Report1->getMoneyOutExists());
        $this->assertEquals('incomplete', $moneyOutSectionStateStatus);
    }

    public function testMoneyCategories(): void
    {
        self::$report103 = self::fixtures()->getReportById(self::$report103->getId()) ?? throw new \LogicException('Bad fixture setup');

        $url = '/report/' . self::$report103->getId();

        $this->assertCount(15, self::$report103->getMoneyShortCategories());

        // check default
        $q = http_build_query(['groups' => [
            'moneyShortCategoriesIn',
            'moneyShortCategoriesOut',
        ]]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertCount(7, $data['money_short_categories_in']);
        $this->assertCount(8, $data['money_short_categories_out']);

        // PUT
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'money_short_categories_in' => [
                    ['type_id' => 'state_pension_and_benefit', 'present' => true],
                    ['type_id' => 'bequests', 'present' => false],
                ],
                'money_short_categories_out' => [
                    ['type_id' => 'accomodation_costs', 'present' => true],
                    ['type_id' => 'care_fees', 'present' => false],
                ],
            ],
        ]);

        // GET and assert
        $q = http_build_query(['groups' => [
            'moneyShortCategoriesIn',
            'moneyShortCategoriesOut',
        ]]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals('state_pension_and_benefit', $data['money_short_categories_in'][0]['type_id']);
        $this->assertEquals(true, $data['money_short_categories_in'][0]['present']);

        $this->assertEquals('accomodation_costs', $data['money_short_categories_out'][7]['type_id']);
        $this->assertEquals(true, $data['money_short_categories_out'][7]['present']);

        $this->assertEquals('care_fees', $data['money_short_categories_out'][8]['type_id']);
        $this->assertEquals(false, $data['money_short_categories_out'][8]['present']);
    }

    public function testAddChecklistWithSaveProgress(): void
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/checked';

        $urlSubmit = '/report/' . self::$report1->getId() . '/submit';
        // submit
        $this->assertJsonRequest('PUT', $urlSubmit, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30',
                'agreed_behalf_deputy' => 'only_deputy',
                'agreed_behalf_deputy_explanation' => 'should not be saved',
            ],
        ]);

        // add new report checklist
        $checklistId = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'button_clicked' => 'save', // Save further information
                'reporting_period_accurate' => 'yes',
                'contact_details_upto_date' => 'yes',
                'deputy_full_name_accurate_in_sirius' => 'yes',
                'decisions_satisfactory' => 'yes',
                'consultations_satisfactory' => 'yes',
                'care_arrangements' => 'yes',
                'assets_declared_and_managed' => null,
                'debts_managed' => 'yes',
                'open_closing_balances_match' => 'yes',
                'accounts_balance' => 'yes',
                'money_movements_acceptable' => 'yes',
                'bond_adequate' => 'yes',
                'bond_order_match_sirius' => 'yes',
                'future_significant_decisions' => 'yes',
                'has_deputy_raised_concerns' => 'no',
                'case_worker_satisified' => 'yes',
            ],
        ])['data']['checklist'];

        // assert creation
        $report = self::fixtures()->getReportById($reportId);

        $checklist = $report->getChecklist();
        $this->assertNotNull($checklist);

        $this->assertEquals($checklistId, $checklist->getId());
        $this->assertEquals('yes', $checklist->getReportingPeriodAccurate());
        $this->assertEquals('yes', $checklist->getContactDetailsUptoDate());
        $this->assertEquals('yes', $checklist->getDeputyFullNameAccurateInSirius());
        $this->assertEquals('yes', $checklist->getDecisionsSatisfactory());
        $this->assertEquals('yes', $checklist->getConsultationsSatisfactory());
        $this->assertEquals('yes', $checklist->getCareArrangements());
        $this->assertEquals(null, $checklist->getAssetsDeclaredAndManaged());
        $this->assertEquals('yes', $checklist->getDebtsManaged());
        $this->assertEquals('yes', $checklist->getOpenClosingBalancesMatch());
        $this->assertEquals('yes', $checklist->getAccountsBalance());
        $this->assertEquals('yes', $checklist->getMoneyMovementsAcceptable());
        $this->assertEquals('yes', $checklist->getBondAdequate());
        $this->assertEquals('yes', $checklist->getBondOrderMatchSirius());
        $this->assertEquals('yes', $checklist->getFutureSignificantDecisions());
        $this->assertEquals('no', $checklist->getHasDeputyRaisedConcerns());
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisfied());
    }

    public function testAddChecklistWithFurtherInformation(): void
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/checked';

        $urlSubmit = '/report/' . $reportId . '/submit';
        // submit
        $this->assertJsonRequest('PUT', $urlSubmit, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30',
                'agreed_behalf_deputy' => 'only_deputy',
                'agreed_behalf_deputy_explanation' => 'should not be saved',
            ],
        ]);

        // add new report checklist
        $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'button_clicked' => 'save', // Save further information
                'reporting_period_accurate' => 'yes',
                'contact_details_upto_date' => 1,
                'deputy_full_name_accurate_in_sirius' => 1,
                'decisions_satisfactory' => 'yes',
                'consultations_satisfactory' => 'yes',
                'care_arrangements' => 'yes',
                'assets_declared_and_managed' => 'na',
                'debts_managed' => 'yes',
                'open_closing_balances_match' => 'yes',
                'accounts_balance' => 'yes',
                'money_movements_acceptable' => 'yes',
                'bond_adequate' => 'yes',
                'bond_order_match_sirius' => 'yes',
                'future_significant_decisions' => 'yes',
                'has_deputy_raised_concerns' => 'no',
                'case_worker_satisified' => 'yes',
            ],
        ])['data']['checklist'];

        // add new report checklist
        $report = self::fixtures()->getReportById($reportId);
        $checklist = $report->getChecklist();
        $this->assertNotNull($checklist);
        $checklistId = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'id' => $checklist->getId(),
                'button_clicked' => 'saveFurtherInformation',
                'save_further_information' => '', // Save further information
                'further_information_received' => 'Some more info',
                'reporting_period_accurate' => 'yes',
                'contact_details_upto_date' => 1,
                'deputy_full_name_accuratein_casrec' => 1,
                'decisions_satisfactory' => 'yes',
                'consultations_satisfactory' => 'yes',
                'care_arrangements' => 'yes',
                'assets_declared_and_managed' => 'na',
                'debts_managed' => 'yes',
                'open_closing_balances_match' => 'yes',
                'accounts_balance' => 'yes',
                'money_movements_acceptable' => 'yes',
                'bond_adequate' => 'yes',
                'bond_order_match_sirius' => 'yes',
                'future_significant_decisions' => 'yes',
                'has_deputy_raised_concerns' => 'no',
                'case_worker_satisified' => 'yes',
            ],
        ])['data']['checklist'];

        // assert creation
        $report = self::fixtures()->getReportById($reportId);
        $this->assertNotNull($report);
        $checklist = $report->getChecklist();
        $this->assertNotNull($checklist);
        $this->assertEquals($checklistId, $checklist->getId());
        $this->assertEquals('yes', $checklist->getReportingPeriodAccurate());
        $this->assertEquals('1', $checklist->getContactDetailsUptoDate());
        $this->assertEquals('1', $checklist->getDeputyFullNameAccurateInSirius());
        $this->assertEquals('yes', $checklist->getDecisionsSatisfactory());
        $this->assertEquals('yes', $checklist->getConsultationsSatisfactory());
        $this->assertEquals('yes', $checklist->getCareArrangements());
        $this->assertEquals('na', $checklist->getAssetsDeclaredAndManaged());
        $this->assertEquals('yes', $checklist->getDebtsManaged());
        $this->assertEquals('yes', $checklist->getOpenClosingBalancesMatch());
        $this->assertEquals('yes', $checklist->getAccountsBalance());
        $this->assertEquals('yes', $checklist->getMoneyMovementsAcceptable());
        $this->assertEquals('yes', $checklist->getBondAdequate());
        $this->assertEquals('yes', $checklist->getBondOrderMatchSirius());
        $this->assertEquals('yes', $checklist->getFutureSignificantDecisions());
        $this->assertEquals('no', $checklist->getHasDeputyRaisedConcerns());
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisfied());

        // assert checklist information created
        $checklistInfo = $checklist->getChecklistInformation();
        $this->assertCount(1, $checklistInfo);

        // assert checklist information saved correctly
        $checklistInfo = $checklistInfo[0];
        $this->assertEquals($checklist->getId(), $checklistInfo->getChecklist()->getId());
        $this->assertNotEmpty($checklistInfo->getId());
        $this->assertNotEmpty($checklistInfo->getCreatedBy());
        $this->assertNotEmpty($checklistInfo->getCreatedOn());
        $this->assertEquals('Some more info', $checklistInfo->getInformation());
    }

    public function testUpdateAndCompleteChecklist(): void
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/checked';

        // submit
        $urlSubmit = '/report/' . $reportId . '/submit';
        $this->assertJsonRequest('PUT', $urlSubmit, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30',
                'agreed_behalf_deputy' => 'only_deputy',
                'agreed_behalf_deputy_explanation' => 'should not be saved',
            ],
        ]);

        // add new report checklist
        $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'button_clicked' => 'save', // Save further information
                'reporting_period_accurate' => 'yes',
                'contact_details_upto_date' => 1,
                'deputy_full_name_accurate_in_sirius' => 1,
                'further_information_received' => 'Some more info',
                'decisions_satisfactory' => 'yes',
                'consultations_satisfactory' => 'yes',
                'care_arrangements' => 'yes',
                'assets_declared_and_managed' => 'na',
                'debts_managed' => 'yes',
                'open_closing_balances_match' => 'yes',
                'accounts_balance' => 'yes',
                'money_movements_acceptable' => 'yes',
                'bond_adequate' => 'yes',
                'bond_order_match_sirius' => 'yes',
                'future_significant_decisions' => 'yes',
                'has_deputy_raised_concerns' => 'no',
                'case_worker_satisified' => 'yes',
            ],
        ])['data']['checklist'];

        // assert submit fails due to missing fields
        $report = self::fixtures()->getReportById($reportId);
        $this->assertNotNull($report);
        $checklist = $report->getChecklist();
        $this->assertNotNull($checklist);

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => false,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'id' => $checklist->getId(),
                'button_clicked' => 'saveAndDownload',
            ],
        ]);

        // clear cache between updates
        self::fixtures()->clear();

        // update report checklist with missing fields
        $checklistId = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'id' => $checklist->getId(),
                'button_clicked' => 'saveAndDownload',
                'lodging_summary' => 'All complete',
                'final_decision' => 'for-review',
            ],
        ])['data']['checklist'];

        // assert creation
        $report = self::fixtures()->getReportById($reportId);
        $this->assertNotNull($report);
        $checklist = $report->getChecklist();
        $this->assertNotNull($checklist);

        $this->assertEquals($checklistId, $checklist->getId());
        $this->assertEquals('yes', $checklist->getReportingPeriodAccurate());
        $this->assertEquals('1', $checklist->getContactDetailsUptoDate());
        $this->assertEquals('1', $checklist->getDeputyFullNameAccurateInSirius());
        $this->assertEquals('yes', $checklist->getDecisionsSatisfactory());
        $this->assertEquals('yes', $checklist->getConsultationsSatisfactory());
        $this->assertEquals('yes', $checklist->getCareArrangements());
        $this->assertEquals('na', $checklist->getAssetsDeclaredAndManaged());
        $this->assertEquals('yes', $checklist->getDebtsManaged());
        $this->assertEquals('yes', $checklist->getOpenClosingBalancesMatch());
        $this->assertEquals('yes', $checklist->getAccountsBalance());
        $this->assertEquals('yes', $checklist->getMoneyMovementsAcceptable());
        $this->assertEquals('yes', $checklist->getBondAdequate());
        $this->assertEquals('yes', $checklist->getBondOrderMatchSirius());
        $this->assertEquals('yes', $checklist->getFutureSignificantDecisions());
        $this->assertEquals('no', $checklist->getHasDeputyRaisedConcerns());
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisfied());
        $this->assertEquals('All complete', $checklist->getLodgingSummary());
        $this->assertEquals('for-review', $checklist->getFinalDecision());

        // assert checklist information created
        $checklistInfo = $checklist->getChecklistInformation();
        $this->assertCount(1, $checklistInfo);

        // assert checklist information saved correctly
        $checklistInfo = $checklistInfo[0];
        $this->assertEquals($checklist->getId(), $checklistInfo->getChecklist()->getId());
        $this->assertNotEmpty($checklistInfo->getId());
        $this->assertNotEmpty($checklistInfo->getCreatedBy());
        $this->assertNotEmpty($checklistInfo->getCreatedOn());
        $this->assertEquals('Some more info', $checklistInfo->getInformation());

        self::fixtures()->clear();
    }

    /** @test */
    public function getQueuedDocumentsUsesSecretAuth(): void
    {
        $return = $this->assertJsonRequest('GET', '/report/all-with-queued-checklists', [
            'mustFail' => true,
            'ClientSecret' => 'WRONG CLIENT SECRET',
            'assertCode' => 403,
            'assertResponseCode' => 403,
            'data' => ['row_limit' => 100],
        ]);

        $this->assertStringContainsString('client secret not accepted', $return['message']);

        $return = $this->assertJsonRequest('GET', '/report/all-with-queued-checklists', [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['row_limit' => 100],
        ]);

        self::assertCount(0, $return['data']);
    }

    public function testRefreshReportCache(): void
    {
        $initialCache = self::$report1->getSectionStatusesCached();
        self::assertEquals(['state' => 'not-started', 'nOfRecords' => 0], $initialCache['documents']);
        self::assertEquals('notStarted', self::$report1->getReportStatusCached());

        $reportId = self::$report1->getId();
        $uri = sprintf('/report/%s/refresh-cache', $reportId);

        $return = $this->assertJsonRequest('POST', $uri, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => ['sectionIds' => ['documents']],
        ]);

        $updatedReport = self::fixtures()->getReportById(self::$report1->getId());

        // We get the same report back that we updated the cache of
        self::assertEquals($updatedReport->getId(), $return['data']['id']);

        // Cache is updated
        $updatedCache = $updatedReport->getSectionStatusesCached();

        self::assertEquals(['state' => 'done', 'nOfRecords' => 1], $updatedCache['documents']);
        self::assertEquals('notFinished', $updatedReport->getReportStatusCached());
    }
}
