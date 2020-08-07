<?php

namespace Tests\AppBundle\ControllerReport;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\Report;
use Tests\AppBundle\Controller\AbstractTestController;

class ReportControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $report103;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;
    private static $tokenPa = null;
    private static $tokenPaAdmin = null;
    private static $tokenPaTeamMember = null;
    private static $casRec1;

    // pa
    private static $pa1;
    private static $pa2Admin;
    private static $caseManager;
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

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // create deputy 1, with 2 submitted reports
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(
            self::$deputy1,
            ['setFirstname' => 'c1', 'setLastname' => 'l1', 'setCaseNumber' => '101010101']
        );
        self::fixtures()->flush();
        self::$report1 = self::fixtures()->createReport(self::$client1, [
            'setStartDate'   => new \DateTime('2014-01-01'),
            'setEndDate'     => new \DateTime('2014-12-31'),
            'setSubmitted'   => true,
            'setSubmittedBy' => self::$deputy1,
        ]);
        self::$report103 = self::fixtures()->createReport(self::$client1, [
            'setStartDate'   => new \DateTime('2015-01-01'),
            'setEndDate'     => new \DateTime('2015-12-31'),
            'setType'        => Report::TYPE_103,
            'setSubmitted'   => true,
            'setSubmittedBy' => self::$deputy1,
        ]);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);

        // pa 1
        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa1Client1 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client1', 'setCaseNumber' => '11111111']);
        self::$pa1Client1Report1 = self::fixtures()->createReport(self::$pa1Client1, ['setType' => Report::TYPE_102_6]);
        self::$pa1Client2 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client2', 'setCaseNumber' => '22222222']);
        self::$pa1Client2Report1 = self::fixtures()->createReport(self::$pa1Client2, ['setType' => Report::TYPE_102_6]);
        self::$pa1Client3 = self::fixtures()->createClient(self::$pa1, ['setFirstname' => 'pa1Client3', 'setCaseNumber' => '33333333']);
        self::$pa1Client3Report1 = self::fixtures()->createReport(self::$pa1Client3, ['setType' => Report::TYPE_102_6]);

        // pa 2
        self::$pa2Admin = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org');
        self::$pa2Client1 = self::fixtures()->createClient(self::$pa2Admin, ['setFirstname' => 'pa2Client1']);
        self::$pa2Client1Report1 = self::fixtures()->createReport(self::$pa2Client1);

        // pa 3
        self::$pa3TeamMember = self::fixtures()->getRepo('User')->findOneByEmail('pa_team_member@example.org');
        self::$pa3Client1 = self::fixtures()->createClient(self::$pa3TeamMember, ['setFirstname' => 'pa3Client1']);
        self::$pa3Client1Report1 = self::fixtures()->createReport(self::$pa3Client1);

        $pa1Org = self::fixtures()->createOrganisation('Example', 'example3941.org', true);
        $pa2Org = self::fixtures()->createOrganisation('Example', 'example4032.org', true);
        $pa3Org = self::fixtures()->createOrganisation('Example', 'example1194.org', true);
        self::fixtures()->flush();
        self::fixtures()->addClientToOrganisation(self::$pa1Client1->getId(), $pa1Org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa1->getId(), $pa1Org->getId());
        self::fixtures()->addClientToOrganisation(self::$pa2Client1->getId(), $pa2Org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa2Admin->getId(), $pa2Org->getId());
        self::fixtures()->addClientToOrganisation(self::$pa3Client1->getId(), $pa3Org->getId());
        self::fixtures()->addUserToOrganisation(self::$pa3TeamMember->getId(), $pa3Org->getId());

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setUp(): void
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenPa = $this->loginAsPa();
            self::$tokenPaAdmin = $this->loginAsPaAdmin();
            self::$tokenPaTeamMember = $this->loginAsPaTeamMember();
        }
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
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'client'     => ['id' => self::$client1->getId()],
                'start_date' => '2016-01-01',
                'end_date'   => '2016-12-31',
            ],
        ])['data']['report'];

        self::fixtures()->clear();

        // assert creation
        $report = self::fixtures()->getReportById($reportId);
        /* @var $report \AppBundle\Entity\Report\Report */
        $this->assertEquals(self::$client1->getId(), $report->getClient()->getId());
        $this->assertEquals('2016-01-01', $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2016-12-31', $report->getEndDate()->format('Y-m-d'));


        self::fixtures()->flush();

        return $report->getId();
    }

    public function testGetByIdAuth()
    {
        $url = '/report/' . self::$report1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testGetByIdAuthPa()
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

    public function testGetByIdAcl()
    {
        $url2 = '/report/' . self::$report2->getId();

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetById()
    {
        $url = '/report/' . self::$report1->getId();

        $q = http_build_query(['groups' => ['report', 'report-client', 'client']]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];
        $this->assertArrayHasKey('report_seen', $data);
        $this->assertArrayNotHasKey('transactions', $data);
        $this->assertArrayNotHasKey('debts', $data);
        $this->assertArrayNotHasKey('fees', $data);
        $this->assertEquals(self::$report1->getId(), $data['id']);
        $this->assertEquals(self::$client1->getId(), $data['client']['id']);
        $this->assertEquals(true, $data['submitted']);
        $this->assertArrayHasKey('start_date', $data);
        $this->assertArrayHasKey('end_date', $data);

        // assert decisions
        $data = $this->assertJsonRequest('GET', $url . '?groups=decision', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];
        $this->assertArrayHasKey('decisions', $data);

        // assert assets
        $data = $this->assertJsonRequest('GET', $url . '?groups=asset', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];
        $this->assertArrayHasKey('assets', $data);

        // assert debts
        $data = $this->assertJsonRequest('GET', $url . '?groups=debt', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];
        $this->assertArrayHasKey('debts', $data);

        // assert fees
        $data = $this->assertJsonRequest('GET', $url . '?groups=fee', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];
        $this->assertArrayHasKey('fees', $data);

        // assert report-submitted-by + user info
        $data = $this->assertJsonRequest('GET', $url . '?groups=report-submitted-by', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals(self::$deputy1->getId(), $data['submitted_by']['id']);
        $this->assertEquals('deputy@example.org', $data['submitted_by']['email']);

        // assert status
        $data = $this->assertJsonRequest('GET', $url . '?groups=status', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data']['status'];

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
            $this->assertArrayHasKey('state', $data[$key]);
            $this->assertArrayHasKey('nOfRecords', $data[$key]);
        }

        //$this->assertArrayHasKey('balance_matches', $data); //TODO check why failing
        $this->assertArrayHasKey('status', $data);
    }

    /**
     * @depends testAdd
     */
    public function testSubmit($reportId)
    {
        $url = '/report/' . self::$report1->getId() . '/submit';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', '/report/' . self::$report2->getId() . '/submit', self::$tokenDeputy);

        $report = self::fixtures()->clear()->getReportById($reportId);

        // add one document
        $document = new Document($report);
        $document->setFileName('file2.pdf')->setStorageReference('storageref1');
        self::fixtures()->persist($document)->flush();
        $this->assertEquals(false, $report->getSubmitted());

        $url = '/report/' . $reportId . '/submit';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'submit_date'                      => '2015-12-30',
                'agreed_behalf_deputy'             => 'only_deputy',
                'agreed_behalf_deputy_explanation' => 'should not be saved',
            ],
        ]);

        // assert account created with transactions
        $report = self::fixtures()->clear()->getReportById($reportId);
        /* @var $report \AppBundle\Entity\Report\Report */
        $this->assertEquals(true, $report->getSubmitted());
        $this->assertEquals(self::$deputy1->getId(), $report->getSubmittedBy()->getId());
        $this->assertEquals('only_deputy', $report->getAgreedBehalfDeputy());
        $this->assertEquals(null, $report->getAgreedBehalfDeputyExplanation());
        $this->assertEquals('2015-12-30', $report->getSubmitDate()->format('Y-m-d'));

        // assert submission is created
        $data = $this->assertJsonRequest('GET', '/report-submission?status=pending', [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
        ])['data'];
        $this->assertEquals(['new' => 0, 'pending' => 1, 'archived' => 0], $data['counts']);
        $this->assertEquals('file2.pdf', $data['records'][0]['documents'][0]['file_name']);

        return $report->getId();
    }

    /**
     * @depends testAdd
     */
    public function testUnsubmit($reportId)
    {
        $url = '/report/' . $reportId . '/unsubmit';

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);

        // assert get
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
            'data'        => [
                'un_submit_date'            => '2018-01-01',
                'due_date'                  => '2019-01-01',
                'start_date'                => '2019-02-01',
                'end_date'                  => '2019-03-01',
                'unsubmitted_sections_list' => 'decisions,contacts',
            ],
        ]);

        // both
        $q = http_build_query(['groups' => ['report']]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', '/report/' . $reportId . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
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
        $url = '/report/' . self::$report1->getId();

        $this->assertEndpointNeedsAuth('PUT', $url);
    }

    public function testUpdateAcl()
    {
        $url2 = '/report/' . self::$report2->getId();

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testAdd
     */
    public function testUpdate($reportId)
    {
        //        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId;

        self::fixtures()->getReportById($reportId)->setDueDate(new \DateTime('2016-11-30'));
        self::fixtures()->flush()->clear();

        // assert get
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'start_date'                   => '2016-01-01',
                'end_date'                     => '2016-11-30',
                'balance_mismatch_explanation' => 'bme',
            ],
        ]);

        // both
        $q = http_build_query(['groups' => ['report']]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals('2016-01-01', $data['start_date']);
        $this->assertEquals('2016-11-30', $data['end_date']);
        $this->assertEquals('2017-01-25', $data['due_date']);

        // repeat test with new end date beyond 13th November 2019
        // assert put new end date
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'start_date'                   => '2019-01-01',
                'end_date'                     => '2019-11-30',
                'balance_mismatch_explanation' => 'bme',
            ],
        ]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals('2019-01-01', $data['start_date']);
        $this->assertEquals('2019-11-30', $data['end_date']);
        $this->assertEquals('2019-12-21', $data['due_date']);
    }

    public function testDebts()
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId;

        // "yes"
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'has_debts' => 'yes',
                'debts'     => [
                    ['debt_type_id' => 'care-fees', 'amount' => 1, 'more_details' => 'should not be saved'],
                    ['debt_type_id' => 'credit-cards', 'amount' => 2, 'more_details' => ''],
                    ['debt_type_id' => 'loans', 'amount' => 3, 'more_details' => ''],
                    ['debt_type_id' => 'other', 'amount' => 4, 'more_details' => 'md'],
                ],
            ],
        ]);

        $q = http_build_query(['groups' => ['debt']]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
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
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'has_debts' => 'no',
                'debts'     => [],
            ],
        ]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
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
        $url = '/report/' . $reportId;

        // save 2 fees and check they are retrieved
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenPa,
            'data'        => [
                'reason_for_no_fees' => null,
                'fees'               => [
                    ['fee_type_id' => 'annual-management-fee', 'amount' => 1.1, 'more_details' => 'should be ignored'],
                    ['fee_type_id' => 'travel-costs', 'amount' => 1.2, 'more_details' => 'tc.md'],
                ],
            ],
        ]);

        $q = http_build_query(['groups' => ['fee']]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenPa,
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
            'AuthToken'   => self::$tokenPa,
            'data'        => [
                'reason_for_no_fees' => 'rfnf',
                'fees'               => [],
            ],
        ]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenPa,
        ])['data'];

        $this->assertEquals('rfnf', $data['reason_for_no_fees']);
        $this->assertCount(count(Fee::$feeTypeIds), $data['fees']);
        $this->assertEquals(0, $data['fees_total']);
        $this->assertEquals('no', $data['has_fees']);
    }

    public function testActions()
    {
        $url = '/report/' . self::$report1->getId();

        // PUT
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'action_more_info'         => 'yes',
                'action_more_info_details' => 'md2',
            ],
        ]);

        // GET and assert
        $q = http_build_query(['groups' => [
            'action-more-info',
        ]]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals('yes', $data['action_more_info']);
        $this->assertEquals('md2', $data['action_more_info_details']);
    }

    public function testMoneyCategories()
    {
        $url = '/report/' . self::$report103->getId();

        self::$report103 = self::fixtures()->getReportById(self::$report103->getId());

        $this->assertCount(15, self::$report103->getMoneyShortCategories());

        // check default
        $q = http_build_query(['groups' => [
            'moneyShortCategoriesIn',
            'moneyShortCategoriesOut',
        ]]);
        $data = $this->assertJsonRequest('GET', $url . '?' . $q, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
        ])['data'];

        $this->assertCount(7, $data['money_short_categories_in']);
        $this->assertCount(8, $data['money_short_categories_out']);


        // PUT
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'money_short_categories_in'  => [
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
            'AuthToken'   => self::$tokenDeputy,
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
        $url = '/report/' . $reportId . '/checked';

        // add new report checklist
        $checklistId = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
            'data'        => [
                'button_clicked' => 'save', // Save further information
                'reporting_period_accurate' => 'yes',
                'contact_details_upto_date' => 1,
                'deputy_full_name_accurate_in_casrec' => 1,
                'decisions_satisfactory' => 'yes',
                'consultations_satisfactory' => 'yes',
                'care_arrangements' => 'yes',
                'assets_declared_and_managed' => 'na',
                'debts_managed' => 'yes',
                'open_closing_balances_match' => 'yes',
                'accounts_balance' => 'yes',
                'money_movements_acceptable' => 'yes',
                'bond_adequate' => 'yes',
                'bond_order_match_casrec' => 'yes',
                'future_significant_decisions' => 'yes',
                'has_deputy_raised_concerns' => 'no',
                'case_worker_satisified' => 'yes',
            ],
        ])['data']['checklist'];

        // assert creation
        /* @var $report \AppBundle\Entity\Report\Report */
        $report = self::fixtures()->getReportById($reportId);
        /* @var $checklist \AppBundle\Entity\Report\Checklist */
        $checklist = $report->getChecklist();
        $this->assertEquals($checklistId, $checklist->getId());
        $this->assertEquals('yes', $checklist->getReportingPeriodAccurate());
        $this->assertEquals('1', $checklist->getContactDetailsUptoDate());
        $this->assertEquals('1', $checklist->getDeputyFullNameAccurateinCasrec());
        $this->assertEquals('yes', $checklist->getDecisionsSatisfactory());
        $this->assertEquals('yes', $checklist->getConsultationsSatisfactory());
        $this->assertEquals('yes', $checklist->getCareArrangements());
        $this->assertEquals('na', $checklist->getAssetsDeclaredAndManaged());
        $this->assertEquals('yes', $checklist->getDebtsManaged());
        $this->assertEquals('yes', $checklist->getOpenClosingBalancesMatch());
        $this->assertEquals('yes', $checklist->getAccountsBalance());
        $this->assertEquals('yes', $checklist->getMoneyMovementsAcceptable());
        $this->assertEquals('yes', $checklist->getBondAdequate());
        $this->assertEquals('yes', $checklist->getBondOrderMatchCasrec());
        $this->assertEquals('yes', $checklist->getFutureSignificantDecisions());
        $this->assertEquals('no', $checklist->getHasDeputyRaisedConcerns());
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisified());
    }

    public function testAddChecklistWithFurtherInformation()
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/checked';
        $report = self::fixtures()->getReportById($reportId);

        // add new report checklist
        $checklistId = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
            'data'        => [
                'id'    => $report->getChecklist()->getId(),
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
                'bond_order_match_casrec' => 'yes',
                'future_significant_decisions' => 'yes',
                'has_deputy_raised_concerns' => 'no',
                'case_worker_satisified' => 'yes',
            ],
        ])['data']['checklist'];

        // assert creation
        /* @var $report \AppBundle\Entity\Report\Report */
        $report = self::fixtures()->getReportById($reportId);
        /* @var $checklist \AppBundle\Entity\Report\Checklist */
        $checklist = $report->getChecklist();
        $this->assertEquals($checklistId, $checklist->getId());
        $this->assertEquals('yes', $checklist->getReportingPeriodAccurate());
        $this->assertEquals('1', $checklist->getContactDetailsUptoDate());
        $this->assertEquals('1', $checklist->getDeputyFullNameAccurateinCasrec());
        $this->assertEquals('yes', $checklist->getDecisionsSatisfactory());
        $this->assertEquals('yes', $checklist->getConsultationsSatisfactory());
        $this->assertEquals('yes', $checklist->getCareArrangements());
        $this->assertEquals('na', $checklist->getAssetsDeclaredAndManaged());
        $this->assertEquals('yes', $checklist->getDebtsManaged());
        $this->assertEquals('yes', $checklist->getOpenClosingBalancesMatch());
        $this->assertEquals('yes', $checklist->getAccountsBalance());
        $this->assertEquals('yes', $checklist->getMoneyMovementsAcceptable());
        $this->assertEquals('yes', $checklist->getBondAdequate());
        $this->assertEquals('yes', $checklist->getBondOrderMatchCasrec());
        $this->assertEquals('yes', $checklist->getFutureSignificantDecisions());
        $this->assertEquals('no', $checklist->getHasDeputyRaisedConcerns());
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisified());

        // assert checklist information created
        /* @var $checklist \AppBundle\Entity\Report\Checklist */
        $checklistInfo = $checklist->getChecklistInformation();
        $this->assertCount(1, $checklistInfo);

        // assert checklist information saved correctly
        $checklistInfo = $checklistInfo[0];
        /** @var $checklistInfo \AppBundle\Entity\Report\ChecklistInformation * */
        $this->assertEquals($checklist->getId(), $checklistInfo->getChecklist()->getId());
        $this->assertNotEmpty($checklistInfo->getId());
        $this->assertNotEmpty($checklistInfo->getCreatedBy());
        $this->assertNotEmpty($checklistInfo->getCreatedOn());
        $this->assertEquals('Some more info', $checklistInfo->getInformation());
    }

    public function testUpdateAndCompleteChecklist()
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/checked';
        $report = self::fixtures()->getReportById($reportId);

        // assert submit fails due to missing fields
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => false,
            'AuthToken'   => self::$tokenAdmin,
            'data'        => [
                'id'    => $report->getChecklist()->getId(),
                'button_clicked' => 'saveAndDownload',
            ]
        ]);

        // clear cache between updates
        self::fixtures()->clear();

        // update report checklist with missing fields
        $checklistId = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
            'data'        => [
                'id'    => $report->getChecklist()->getId(),
                'button_clicked' => 'saveAndDownload',
                'lodging_summary' => 'All complete',
                'final_decision' => 'for-review'
            ],
        ])['data']['checklist'];

        // assert creation
        /* @var $report \AppBundle\Entity\Report\Report */
        $report = self::fixtures()->getReportById($reportId);
        /* @var $checklist \AppBundle\Entity\Report\Checklist */
        $checklist = $report->getChecklist();

        $this->assertEquals($checklistId, $checklist->getId());
        $this->assertEquals('yes', $checklist->getReportingPeriodAccurate());
        $this->assertEquals('1', $checklist->getContactDetailsUptoDate());
        $this->assertEquals('1', $checklist->getDeputyFullNameAccurateinCasrec());
        $this->assertEquals('yes', $checklist->getDecisionsSatisfactory());
        $this->assertEquals('yes', $checklist->getConsultationsSatisfactory());
        $this->assertEquals('yes', $checklist->getCareArrangements());
        $this->assertEquals('na', $checklist->getAssetsDeclaredAndManaged());
        $this->assertEquals('yes', $checklist->getDebtsManaged());
        $this->assertEquals('yes', $checklist->getOpenClosingBalancesMatch());
        $this->assertEquals('yes', $checklist->getAccountsBalance());
        $this->assertEquals('yes', $checklist->getMoneyMovementsAcceptable());
        $this->assertEquals('yes', $checklist->getBondAdequate());
        $this->assertEquals('yes', $checklist->getBondOrderMatchCasrec());
        $this->assertEquals('yes', $checklist->getFutureSignificantDecisions());
        $this->assertEquals('no', $checklist->getHasDeputyRaisedConcerns());
        $this->assertEquals('yes', $checklist->getCaseWorkerSatisified());
        $this->assertEquals('All complete', $checklist->getLodgingSummary());
        $this->assertEquals('for-review', $checklist->getFinalDecision());

        // assert checklist information created
        /* @var $checklist \AppBundle\Entity\Report\Checklist */
        $checklist = $report->getChecklist();
        $checklistInfo = $checklist->getChecklistInformation();
        $this->assertCount(1, $checklistInfo);

        // assert checklist information saved correctly
        $checklistInfo = $checklistInfo[0];
        /** @var $checklistInfo \AppBundle\Entity\Report\ChecklistInformation * */
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
            'data' => ['row_limit' => 100]
        ]);

        $this->assertStringContainsString('client secret not accepted', $return['message']);

        $return = $this->assertJsonRequest('GET', '/report/all-with-queued-checklists', [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['row_limit' => 100]
        ]);

        self::assertCount(0, $return['data']);
    }
}
