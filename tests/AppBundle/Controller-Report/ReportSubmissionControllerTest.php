<?php

namespace Tests\AppBundle\Controller\Report;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use Doctrine\Tests\ORM\Mapping\User;
use Tests\AppBundle\Controller\AbstractTestController;

class ReportSubmissionControllerTest extends AbstractTestController
{
    private static $pa1;
    private static $pa2;
    private static $deputy1;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa2 = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org');
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        // create 5 submitted reports
        for ($i=0; $i<5; $i++) {
            $client = self::fixtures()->createClient(
                self::$pa1,
                ['setFirstname' => "c{$i}", 'setLastname' => "l{$i}", 'setCaseNumber' => "100000{$i}"]
            );
            $report = self::fixtures()->createReport($client, [
                'setStartDate'   => new \DateTime('2014-01-01'),
                'setEndDate'     => new \DateTime('2014-12-31'),
                'setSubmitted'   => true,
                'setSubmittedBy' => self::$pa1, //irrelevant for assertions
                'setSubmitDate'  => new \DateTime('2015-01-01')
            ]);
            // create submission
            $submission = new ReportSubmission($report, ($i<3) ? self::$pa2 : self::$deputy1);
            // add documents, needed for future tests
            $document = new Document($report);
            $document->setFileName('file1.pdf')->setStorageReference('storageref1')->setReportSubmission($submission);
            self::fixtures()->persist($document, $submission);
        }

        self::fixtures()->flush()->clear();
    }

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testGetAllWithFiltersGetOneArchive()
    {
        $reportsGetAllRequest = function (array $params = []) {
            $url = '/report-submission?' . http_build_query($params);

            return $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken'   => self::$tokenAdmin,
            ])['data'];
        };

        $this->assertEndpointNeedsAuth('GET', '/report-submission');
        $this->assertEndpointNotAllowedFor('GET', '/report-submission', self::$tokenDeputy);

        // assert submission (only one expected)
        $data = $reportsGetAllRequest(['status'=>'new']);
        $this->assertEquals(['new'=>5, 'archived'=>0], $data['counts']);

        $submission4 = $this->getSubmissionByCaseNumber($data['records'], '1000004');
        $this->assertNotEmpty($submission4['id']);
        $this->assertNotEmpty($submission4['report']['type']);
        $this->assertNotEmpty($submission4['report']['start_date']);
        $this->assertNotEmpty($submission4['report']['end_date']);
        $this->assertNotEmpty($submission4['report']['client']['case_number']);
        $this->assertNotEmpty($submission4['report']['client']['firstname']);
        $this->assertNotEmpty($submission4['report']['client']['lastname']);
        $this->assertEquals('file1.pdf', $submission4['documents'][0]['file_name']);
        $this->assertNotEmpty($submission4['created_by']['firstname']);
        $this->assertNotEmpty($submission4['created_by']['lastname']);
        $this->assertNotEmpty($submission4['created_by']['role_name']);
        $this->assertNotEmpty($submission4['created_on']);
        $this->assertArrayHasKey('archived_by', $submission4);

        // test getOne endpoint
        $data = $this->assertJsonRequest('GET', '/report-submission/' . $submission4['id'], [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
        ])['data'];
        $this->assertEquals($submission4['id'], $data['id']);
        $this->assertEquals('storageref1', $data['documents'][0]['storage_reference']);

        // archive 1st submission
        $data = $this->assertJsonRequest('PUT', '/report-submission/' . $submission4['id'], [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
            'data' => ['archive'=>true]
        ])['data'];
        $this->assertEquals($submission4['id'], $data);

        // check counts after submission
        $data = $reportsGetAllRequest([]);
        $this->assertEquals(['new'=>4, 'archived'=>1], $data['counts']);
        $this->assertCount(5, $data['records']);

        // check filters and counts
        $data = $reportsGetAllRequest(['q'=>'1000000']);
        $this->assertEquals(['new'=>1, 'archived'=>0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $data = $reportsGetAllRequest(['q'=>'1000000', 'status'=>'new']);
        $this->assertEquals(['new'=>1, 'archived'=>0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $this->assertEquals(['new'=>1, 'archived'=>0], $reportsGetAllRequest(['status'=>'new', 'q'=>'c0'])['counts']); // client name
        $this->assertEquals(['new'=>1, 'archived'=>0], $reportsGetAllRequest(['status'=>'new', 'q'=>'l0'])['counts']); //client surname
        $this->assertEquals(['new'=>4, 'archived'=>1], $reportsGetAllRequest(['status'=>'new', 'q'=>'test'])['counts']); // deputy name
        $this->assertEquals(['new'=>1, 'archived'=>1], $reportsGetAllRequest(['created_by_role'=>'ROLE_LAY_DEPUTY'])['counts']);
        // since this filter works with the role being a prefix, ROLE_PA would include all the ROLE_PA* ones
        // a better version would calculate all the inheritance
        $this->assertEquals(['new'=>3, 'archived'=>0], $reportsGetAllRequest(['created_by_role'=>'ROLE_PA'])['counts']);

        // check pagination and limit
        $submissions = $reportsGetAllRequest(['status'=>'new', 'q'=>'test'])['records'];
        $this->assertEquals(['1000000', '1000001','1000002','1000003'], $this->getOrderedCaseNumbersFromSubmissions($submissions));

        $submissions = $reportsGetAllRequest(['status'=>'new', 'q'=>'test', 'offset'=>1, 'limit'=>2])['records'];
        $this->assertEquals(['1000001', '1000002'], $this->getOrderedCaseNumbersFromSubmissions($submissions));
    }

    private function getOrderedCaseNumbersFromSubmissions($submissions)
    {
        $ret = array_map(function ($submission) {
            return $submission['report']['client']['case_number'];
        }, $submissions);

        sort($ret);
        return $ret;
    }

    private function getSubmissionByCaseNumber(array $submissions, $caseNumber)
    {
        $ret = array_filter($submissions, function ($submission) use ($caseNumber) {
            return $submission['report']['client']['case_number'] == $caseNumber;
        });

        return array_shift($ret);
    }

    /**
     * @dataProvider getDateRangeThresholds
     * @param $fromDate
     * @param $toDate
     * @param array $expectedOutcomes
     */
    public function testGetCaserecDataRetrievesWithinGivenDateRangesInclusive($fromDate, $toDate, array $expectedOutcomes)
    {
        $this->updateReportSubmissionByIdWithNewDateTime(1, '2018-01-01 12:00:00');
        $this->updateReportSubmissionByIdWithNewDateTime(2, '2018-01-31 12:00:00');
        self::fixtures()->flush();

        $data = $this->makeRequestAndReturnResults(
            '/report-submission/casrec_data',
            ['fromDate[date]' => $fromDate, 'toDate[date]' => $toDate]
        );

        $this->assertEquals($expectedOutcomes['count'], count($data));

        foreach ($expectedOutcomes['caseNumbers'] as $expectedCaseNumber) {
            $this->assertResponseIncludesReportWithCaseNumber($data, $expectedCaseNumber);
        }
    }

    /**
     * @return array
     */
    public function getDateRangeThresholds()
    {
        return [
            [
                'fromDate' => '2018-01-01 12:00:00',
                'toDate' => '2018-01-31 12:00:00',
                'expectedOutcomes' => [
                    'count' => 2,
                    'caseNumbers' => ['1000000', '1000001']
                ]
            ],
            [
                'fromDate' => '2017-12-31 23:59:59',
                'toDate' => '2018-02-01 00:00:00',
                'expectedOutcomes' => [
                    'count' => 2,
                    'caseNumbers' => ['1000000', '1000001']
                ]
            ],
            [
                'fromDate' => '2018-01-01 12:00:01',
                'toDate' => '2018-01-31 12:00:00',
                'expectedOutcomes' => [
                    'count' => 1,
                    'caseNumbers' => ['1000001']
                ]
            ],
            [
                'fromDate' => '2018-01-01 12:00:00',
                'toDate' => '2018-01-31 09:59:59',
                'expectedOutcomes' => [
                    'count' => 1,
                    'caseNumbers' => ['1000000']
                ]
            ]
        ];
    }

    public function testGetCaserecDataRetrievesUpToNowIfNotGivenToDate()
    {
        $reportId = 1;
        $this->updateReportSubmissionByIdWithNewDateTime($reportId, 'today');
        self::fixtures()->flush();

        $result = $this->makeRequestAndReturnResults('/report-submission/casrec_data', []);
        $this->assertResponseIncludesReportWithCaseNumber($result, '1000000');
    }

    /**
     * @param $id
     * @param $date
     */
    private function updateReportSubmissionByIdWithNewDateTime($id, $date)
    {
        $entity = self::fixtures()->getRepo('Report\ReportSubmission')->findOneById($id);
        $entity->setCreatedOn(new \DateTime($date));

        self::fixtures()->persist($entity);
    }

    /**
     * @param $endpoint
     * @param array $params
     * @return mixed
     */
    private function makeRequestAndReturnResults($endpoint, array $params)
    {
        $url = sprintf('%s?%s', $endpoint, http_build_query($params));

        $response = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenAdmin,
        ]);

        array_shift($response['data']);

        return $response['data'];
    }

    /**
     * @param $data
     * @param $caseNumber
     */
    private function assertResponseIncludesReportWithCaseNumber($data, $caseNumber)
    {
        $testPassed = false;
        foreach ($data as $row) {
            if ($row[0] == $caseNumber) {
                $testPassed = true;
                break;
            }
        }

        $this->assertTrue($testPassed, sprintf('Response does not contain report for case number %s', $caseNumber));
    }
}
