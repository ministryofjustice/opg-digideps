<?php

namespace App\Tests\Integration\ControllerReport;

use App\Entity\PreRegistration;
use App\Entity\Report\Checklist;
use App\Entity\Report\ChecklistInformation;
use App\Entity\Report\Document;
use App\Entity\Report\Fee;
use App\Entity\Report\Report;
use App\Tests\Integration\Controller\AbstractTestController;

class ReportControllerTest extends AbstractTestController
{
    private static $preRegistration1;
    private static $preRegistration3;
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $report103;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $tokenAdmin;
    private static $tokenDeputy;
    private static $tokenPa;
    private static $tokenPaAdmin;
    private static $tokenPaTeamMember;
    private static $client3;

    // pa
    private static $pa1;
    private static $pa2Admin;
    private static $pa3TeamMember;
    private static $pa1Client1;
    private static $pa1Client1Report1;
    private static $pa1Client2;
    private static $pa1Client2Report1;
    private static $pa1Client3;
    private static $pa1Client3Report1;
    private static $pa2Client1;
    private static $pa2Client1Report1;
    private static $pa3Client1;
    private static $pa3Client1Report1;

    // new
    private static $clientEdit;
    private static $reportEdit;

    public function setUp(): void
    {
        parent::setUp();

        // create deputy 1, with 2 submitted reports
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(
            self::$deputy1,
            ['setFirstname' => 'c1', 'setLastname' => 'l1', 'setCaseNumber' => '101010101']
        );
        self::$client3 = self::fixtures()->createClient(
            self::$deputy1,
            ['setFirstname' => 'c3', 'setLastname' => 'l3', 'setCaseNumber' => '303030303']
        );
        self::$deputy1->addClient(self::$client1);
        self::$deputy1->addClient(self::$client3);
        self::fixtures()->persist(self::$deputy1);

        self::$preRegistration1 = new PreRegistration([
            'Case' => self::$client1->getCaseNumber(),
            'ClientSurname' => self::$client1->getLastName(),
            'DeputyUid' => (string) self::$deputy1->getDeputyUid(),
            'DeputyFirstname' => self::$deputy1->getFirstname(),
            'DeputySurname' => self::$deputy1->getLastname(),
            'DeputyPostcode' => self::$deputy1->getAddressPostcode(),
            'ReportType' => 'OPG102',
            'MadeDate' => (new \DateTime('2016-01-01'))->format('Y-m-d'),
            'OrderType' => 'pfa',
            'CoDeputy' => false,
            'Hybrid' => 'SINGLE',
        ]);

        // New registration - no old submitted reports therefore report will start from current year
        self::$preRegistration3 = new PreRegistration([
            'Case' => self::$client3->getCaseNumber(),
            'ClientSurname' => self::$client3->getLastName(),
            'DeputyUid' => (string) self::$deputy1->getDeputyUid(),
            'DeputyFirstname' => self::$deputy1->getFirstname(),
            'DeputySurname' => self::$deputy1->getLastname(),
            'DeputyPostcode' => self::$deputy1->getAddressPostcode(),
            'ReportType' => 'OPG102',
            'MadeDate' => (new \DateTime('2017-01-01'))->format('Y-m-d'),
            'OrderType' => 'pfa',
            'CoDeputy' => false,
            'Hybrid' => 'SINGLE',
        ]);

        self::$fixtures->persist(self::$preRegistration1, self::$preRegistration3);

        self::$clientEdit = self::fixtures()->createClient(
            self::$deputy1,
            ['setFirstname' => 'cEdit1', 'setLastname' => 'l1', 'setCaseNumber' => '010101010']
        );
        self::fixtures()->flush();

        self::$report1 = self::fixtures()->createReport(self::$client1, [
            'setStartDate' => new \DateTime('2014-01-01'),
            'setEndDate' => new \DateTime('2014-12-31'),
            'setSubmitted' => true,
            'setSubmittedBy' => self::$deputy1,
            'setWishToProvideDocumentation' => true,
        ]);

        $document = (new Document(self::$report1))
            ->setFileName('test.pdf')
            ->setIsReportPdf(false);

        self::fixtures()->persist($document);
        self::fixtures()->flush();

        self::$reportEdit = self::fixtures()->createReport(self::$clientEdit, [
            'setStartDate' => new \DateTime('2014-01-01'),
            'setEndDate' => new \DateTime('2014-12-31'),
            'setSubmitted' => false,
            'setSubmittedBy' => null,
        ]);
        self::$report103 = self::fixtures()->createReport(self::$client1, [
            'setStartDate' => new \DateTime('2015-01-01'),
            'setEndDate' => new \DateTime('2015-12-31'),
            'setType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
            'setSubmitted' => true,
            'setSubmittedBy' => self::$deputy1,
        ]);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);

        // pa 1
        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa1Client1 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client1', 'setCaseNumber' => '11111111']);
        self::$pa1Client1Report1 = self::fixtures()->createReport(self::$pa1Client1, ['setType' => Report::PA_PFA_HIGH_ASSETS_TYPE]);
        self::$pa1Client2 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client2', 'setCaseNumber' => '22222222']);
        self::$pa1Client2Report1 = self::fixtures()->createReport(self::$pa1Client2, ['setType' => Report::PA_PFA_HIGH_ASSETS_TYPE]);
        self::$pa1Client3 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client3', 'setCaseNumber' => '33333333']);
        self::$pa1Client3Report1 = self::fixtures()->createReport(self::$pa1Client3, ['setType' => Report::PA_PFA_HIGH_ASSETS_TYPE]);

        // pa 2
        self::$pa2Admin = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org');
        self::$pa2Client1 = self::fixtures()->createClient(self::$pa2Admin, ['setFirstname' => 'pa2Client1']);
        self::$pa2Client1Report1 = self::fixtures()->createReport(self::$pa2Client1);

        // pa 3
        self::$pa3TeamMember = self::fixtures()->getRepo('User')->findOneByEmail('pa_team_member@example.org');
        self::$pa3Client1 = self::fixtures()->createClient(self::$pa3TeamMember, ['setFirstname' => 'pa3Client1']);
        self::$pa3Client1Report1 = self::fixtures()->createReport(self::$pa3Client1);

        $pa1Org = self::fixtures()->createOrganisation('Example', rand(1, 9999999).'example.org', true);
        $pa2Org = self::fixtures()->createOrganisation('Example', rand(1, 9999999).'example.org', true);
        $pa3Org = self::fixtures()->createOrganisation('Example', rand(1, 9999999).'example.org', true);
        self::fixtures()->flush();
        self::fixtures()->addClientToOrganisation(self::$pa1Client1->getId(), $pa1Org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa1->getId(), $pa1Org->getId());
        self::fixtures()->addClientToOrganisation(self::$pa2Client1->getId(), $pa2Org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa2Admin->getId(), $pa2Org->getId());
        self::fixtures()->addClientToOrganisation(self::$pa3Client1->getId(), $pa3Org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa3TeamMember->getId(), $pa3Org->getId());

        self::fixtures()->flush()->clear();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenPa = $this->loginAsPa();
            self::$tokenPaAdmin = $this->loginAsPaAdmin();
            self::$tokenPaTeamMember = $this->loginAsPaTeamMember();
        }
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testAddAuth()
    {
        $url = '/report';
        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }

    public function testAddAcl()
    {
        $url = '/report';
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy, [
            'client' => ['id' => self::$client2->getId()],
        ]);
    }

    public function testAdd()
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
        /* @var $report Report */
        $this->assertEquals(self::$client3->getId(), $report->getClient()->getId());

        $currentYear = date('Y');
        $this->assertEquals($currentYear.'-01-01', $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals($currentYear.'-12-31', $report->getEndDate()->format('Y-m-d'));

        self::fixtures()->flush();
    }

    public function testGetByIdAuth()
    {
        $url = '/report/'.self::$report1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointAllowedFor('GET', $url, self::$tokenAdmin);
        $this->assertEndpointAllowedFor('GET', $url, self::$tokenDeputy);
    }

    public function testGetByIdAuthPa()
    {
        $urlReport1 = '/report/'.self::$pa1Client1Report1->getId();
        $urlReport2 = '/report/'.self::$pa2Client1Report1->getId();
        $urlReport3 = '/report/'.self::$pa3Client1Report1->getId();

        $this->assertEndpointAllowedFor('GET', $urlReport1, self::$tokenPa);
        $this->assertEndpointAllowedFor('GET', $urlReport2, self::$tokenPaAdmin);
        $this->assertEndpointAllowedFor('GET', $urlReport3, self::$tokenPaTeamMember);

        $this->assertEndpointNotAllowedFor('GET', $urlReport2, self::$tokenPa);
        $this->assertEndpointNotAllowedFor('GET', $urlReport3, self::$tokenPaAdmin);
        $this->assertEndpointNotAllowedFor('GET', $urlReport1, self::$tokenPaTeamMember);
    }

    public function testGetByIdAcl()
    {
        $url2 = '/report/'.self::$report2->getId();

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetById()
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

        $this->assertEquals(self::$deputy1->getId(), $submittedByData['submitted_by']['id']);
        $this->assertEquals('deputy@example.org', $submittedByData['submitted_by']['email']);

        // assert status
        $statusData = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['status']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data']['status'];

        foreach ([
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
        ] as $key) {
            $this->assertArrayHasKey('state', $statusData[$key]);
            $this->assertArrayHasKey('nOfRecords', $statusData[$key]);
        }

        $this->assertArrayHasKey('status', $statusData);
    }

    public function testSubmit()
    {
        $url = '/report/'.self::$reportEdit->getId().'/submit';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', '/report/'.self::$report2->getId().'/submit', self::$tokenDeputy);

        $report = self::fixtures()->clear()->getReportById(self::$reportEdit->getId());

        // add one document
        $document = new Document($report);
        $document->setFileName('file2.pdf')->setStorageReference('storageref1');
        self::fixtures()->persist($document)->flush();
        $this->assertEquals(false, $report->getSubmitted());

        $url = '/report/'.self::$reportEdit->getId().'/submit';

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
        /* @var $report Report */
        $this->assertEquals(true, $report->getSubmitted());
        $this->assertEquals(self::$deputy1->getId(), $report->getSubmittedBy()->getId());
        $this->assertEquals('only_deputy', $report->getAgreedBehalfDeputy());
        $this->assertEquals(null, $report->getAgreedBehalfDeputyExplanation());
        $this->assertEquals('2015-12-30', $report->getSubmitDate()->format('Y-m-d'));

        // assert submission is created
        $data = $this->assertJsonRequest('GET', '/report-submission?status=pending', [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];
        $this->assertEquals(['new' => 0, 'pending' => 1, 'archived' => 0], $data['counts']);
        $this->assertEquals('file2.pdf', $data['records'][0]['documents'][0]['file_name']);
    }

    public function testUnsubmit()
    {
        $urlSubmit = '/report/'.self::$reportEdit->getId().'/submit';

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

        $url = '/report/'.self::$reportEdit->getId().'/unsubmit';

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
        $data = $this->assertJsonRequest('GET', '/report/'.self::$reportEdit->getId().'?'.$q, [
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

    public function testUpdateAuth()
    {
        $url = '/report/'.self::$report1->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testUpdateAcl()
    {
        $url2 = '/report/'.self::$report2->getId();

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testUpdate()
    {
        $reportId = self::$report1->getId();
        $url = '/report/'.$reportId;

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
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
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
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals('2019-01-01', $data['start_date']);
        $this->assertEquals('2019-11-30', $data['end_date']);
        $this->assertEquals('2019-12-21', $data['due_date']);
    }

    public function testDebts()
    {
        $reportId = self::$report1->getId();
        $url = '/report/'.$reportId;

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
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
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
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
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

    public function testPaFeesEditResetAndTotals()
    {
        $reportId = self::$pa1Client1Report1->getId();
        $url = '/report/'.$reportId;

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
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
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
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
        ])['data'];

        $this->assertEquals('rfnf', $data['reason_for_no_fees']);
        $this->assertCount(count(Fee::$feeTypeIds), $data['fees']);
        $this->assertEquals(0, $data['fees_total']);
        $this->assertEquals('no', $data['has_fees']);
    }

    public function testActions()
    {
        $url = '/report/'.self::$report1->getId();

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
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals('yes', $data['action_more_info']);
        $this->assertEquals('md2', $data['action_more_info_details']);
    }

    public function testMoneyInLowAssetDoesNotExist()
    {
        $reportId = self::$report103->getId();

        $url = '/report/'.$reportId;
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'money_in_exists' => 'No',
            ]])['data'];

        self::$report103 = self::fixtures()->getReportById($reportId);

        $moneyInSectionStateStatusShort = self::fixtures()->getReportFreshSectionStatus(self::$report103, Report::SECTION_MONEY_IN_SHORT)['state'];

        $this->assertEquals('No', self::$report103->getMoneyInExists());
        $this->assertEquals('incomplete', $moneyInSectionStateStatusShort);
    }

    public function testMoneyInLowAssetDoesNotExistWithReason()
    {
        $report = self::fixtures()->getReportById(self::$report103->getId());
        $reportId = $report->getId();

        $report->setMoneyInExists('No');
        self::fixtures()->persist($report)->flush();

        $url = '/report/'.$reportId;

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

    public function testMoneyOutHighAssetExists()
    {
        $reportId = self::$pa1Client1Report1->getId();

        $url = '/report/'.$reportId;
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenPa,
            'data' => [
                'money_out_exists' => 'Yes',
            ]])['data'];

        self::fixtures()->clear();
        self::$pa1Client1Report1 = self::fixtures()->getReportById($reportId);

        $moneyOutSectionStateStatus = self::fixtures()->getReportFreshSectionStatus(self::$pa1Client1Report1, Report::SECTION_MONEY_OUT)['state'];

        $this->assertEquals('Yes', self::$pa1Client1Report1->getMoneyOutExists());
        $this->assertEquals('incomplete', $moneyOutSectionStateStatus);
    }

    public function testMoneyCategories()
    {
        $url = '/report/'.self::$report103->getId();

        self::$report103 = self::fixtures()->getReportById(self::$report103->getId());

        $this->assertCount(15, self::$report103->getMoneyShortCategories());

        // check default
        $q = http_build_query(['groups' => [
            'moneyShortCategoriesIn',
            'moneyShortCategoriesOut',
        ]]);
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
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
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
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

    public function testAddChecklistWithSaveProgress()
    {
        $reportId = self::$report1->getId();
        $url = '/report/'.$reportId.'/checked';

        $urlSubmit = '/report/'.self::$report1->getId().'/submit';
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

        // assert creation
        /* @var $report Report */
        $report = self::fixtures()->getReportById($reportId);
        /* @var $checklist Checklist */
        $checklist = $report->getChecklist();
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
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisified());
    }

    public function testAddChecklistWithFurtherInformation()
    {
        $reportId = self::$report1->getId();
        $url = '/report/'.$reportId.'/checked';

        $urlSubmit = '/report/'.$reportId.'/submit';
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
        $checklistId = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'id' => $report->getChecklist()->getId(),
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
        /* @var $report Report */
        $report = self::fixtures()->getReportById($reportId);
        /* @var $checklist Checklist */
        $checklist = $report->getChecklist();
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
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisified());

        // assert checklist information created
        /* @var $checklist Checklist */
        $checklistInfo = $checklist->getChecklistInformation();
        $this->assertCount(1, $checklistInfo);

        // assert checklist information saved correctly
        $checklistInfo = $checklistInfo[0];
        /* @var $checklistInfo ChecklistInformation * */
        $this->assertEquals($checklist->getId(), $checklistInfo->getChecklist()->getId());
        $this->assertNotEmpty($checklistInfo->getId());
        $this->assertNotEmpty($checklistInfo->getCreatedBy());
        $this->assertNotEmpty($checklistInfo->getCreatedOn());
        $this->assertEquals('Some more info', $checklistInfo->getInformation());
    }

    public function testUpdateAndCompleteChecklist()
    {
        $reportId = self::$report1->getId();
        $url = '/report/'.$reportId.'/checked';

        // submit
        $urlSubmit = '/report/'.$reportId.'/submit';
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

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => false,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'id' => $report->getChecklist()->getId(),
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
                'id' => $report->getChecklist()->getId(),
                'button_clicked' => 'saveAndDownload',
                'lodging_summary' => 'All complete',
                'final_decision' => 'for-review',
            ],
        ])['data']['checklist'];

        // assert creation
        /* @var $report Report */
        $report = self::fixtures()->getReportById($reportId);
        /* @var $checklist Checklist */
        $checklist = $report->getChecklist();

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
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisified());
        $this->assertEquals('All complete', $checklist->getLodgingSummary());
        $this->assertEquals('for-review', $checklist->getFinalDecision());

        // assert checklist information created
        /* @var $checklist Checklist */
        $checklist = $report->getChecklist();
        $checklistInfo = $checklist->getChecklistInformation();
        $this->assertCount(1, $checklistInfo);

        // assert checklist information saved correctly
        $checklistInfo = $checklistInfo[0];
        /* @var $checklistInfo ChecklistInformation * */
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

    /** @test */
    public function refreshReportCache(): void
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
