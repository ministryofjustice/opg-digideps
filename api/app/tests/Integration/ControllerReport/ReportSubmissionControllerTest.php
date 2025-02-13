<?php

namespace App\Tests\Integration\ControllerReport;

use App\Entity\Report\Document;
use App\Entity\Report\ReportSubmission;
use App\TestHelpers\ReportSubmissionHelper;
use App\Tests\Integration\Controller\AbstractTestController;
use Symfony\Component\HttpFoundation\Response;

class ReportSubmissionControllerTest extends AbstractTestController
{
    private static $pa1;
    private static $pa2;
    private static $deputy1;
    private static $tokenSuperAdmin;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();
        self::$pa1 = self::fixtures()->getRepo('User')->findOneByEmail('pa@example.org');
        self::$pa2 = self::fixtures()->getRepo('User')->findOneByEmail('pa_admin@example.org');
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        // create 5 submitted reports
        for ($i = 0; $i < 5; ++$i) {
            $client = self::fixtures()->createClient(
                self::$pa1,
                ['setFirstname' => "c{$i}", 'setLastname' => "l{$i}", 'setCaseNumber' => "100000{$i}"]
            );
            $report = self::fixtures()->createReport($client, [
                'setStartDate' => new \DateTime('2014-01-01'),
                'setEndDate' => new \DateTime('2014-12-31'),
                'setSubmitted' => true,
                'setSubmittedBy' => self::$pa1, // irrelevant for assertions
                'setSubmitDate' => new \DateTime('2015-01-01'),
            ]);
            // create submission
            $submission = new ReportSubmission($report, ($i < 3) ? self::$pa2 : self::$deputy1);
            // add documents, needed for future tests
            $document = new Document($report);
            $document->setFileName('file1.pdf')->setStorageReference('storageref1')->setReportSubmission($submission);

            if (2 === $i) {
                $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
            }

            self::fixtures()->persist($document, $submission);
        }

        self::fixtures()->flush()->clear();

        if (null === self::$tokenAdmin) {
            self::$tokenSuperAdmin = $this->loginAsSuperAdmin();
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testGetAllWithFiltersGetOneArchive()
    {
        $reportsGetAllRequest = function (array $params = []) {
            $url = '/report-submission?'.http_build_query($params);

            return $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenAdmin,
            ])['data'];
        };

        $this->assertEndpointNeedsAuth('GET', '/report-submission');
        $this->assertEndpointNotAllowedFor('GET', '/report-submission', self::$tokenDeputy);

        // assert submission (only one expected)
        $data = $reportsGetAllRequest(['status' => 'new']);
        $this->assertEquals(['new' => 4, 'pending' => 1, 'archived' => 0], $data['counts']);

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
        $data = $this->assertJsonRequest('GET', '/report-submission/'.$submission4['id'], [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];
        $this->assertEquals($submission4['id'], $data['id']);
        $this->assertEquals('storageref1', $data['documents'][0]['storage_reference']);

        // archive 1st submission
        $data = $this->assertJsonRequest('PUT', '/report-submission/'.$submission4['id'], [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => ['archive' => true],
        ])['data'];
        $this->assertEquals($submission4['id'], $data);

        // check counts after submission
        $data = $reportsGetAllRequest([]);
        $this->assertEquals(['new' => 3, 'pending' => 1, 'archived' => 1], $data['counts']);
        $this->assertCount(5, $data['records']);

        // check filters and counts
        $data = $reportsGetAllRequest(['q' => '1000000']);
        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $data = $reportsGetAllRequest(['q' => '1000000', 'status' => 'new']);
        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $data = $reportsGetAllRequest(['q' => '1000002', 'status' => 'new']);
        $this->assertEquals(['new' => 0, 'pending' => 1, 'archived' => 0], $data['counts']);
        $this->assertCount(0, $data['records']);

        $data = $reportsGetAllRequest(['q' => '1000002', 'status' => 'pending']);
        $this->assertEquals(['new' => 0, 'pending' => 1, 'archived' => 0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 0], $reportsGetAllRequest(['status' => 'new', 'q' => 'c0'])['counts']); // client name
        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 0], $reportsGetAllRequest(['status' => 'new', 'q' => 'l0'])['counts']); // client surname
        $this->assertEquals(['new' => 3, 'pending' => 1, 'archived' => 1], $reportsGetAllRequest(['status' => 'new', 'q' => 'test'])['counts']); // deputy name
        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 1], $reportsGetAllRequest(['created_by_role' => 'ROLE_LAY_DEPUTY'])['counts']);
        // since this filter works with the role being a prefix, ROLE_PA would include all the ROLE_PA* ones
        // a better version would calculate all the inheritance
        $this->assertEquals(['new' => 2, 'pending' => 1, 'archived' => 0], $reportsGetAllRequest(['created_by_role' => 'ROLE_PA'])['counts']);

        // check pagination and limit
        $submissions = $reportsGetAllRequest(['status' => 'new', 'q' => 'test'])['records'];
        $this->assertEquals(['1000000', '1000001', '1000003'], $this->getOrderedCaseNumbersFromSubmissions($submissions));

        $submissions = $reportsGetAllRequest(['status' => 'new', 'q' => 'test', 'orderBy' => 'id', 'limit' => 2, 'offset' => 1])['records'];
        $this->assertEquals(['1000000', '1000001'], $this->getOrderedCaseNumbersFromSubmissions($submissions));
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
     */
    public function testGetCaserecDataRetrievesWithinGivenDateRangesInclusive(string $fromDate, string $toDate, array $expectedOutcomes)
    {
        $this->updateReportSubmissionByIdWithNewDateTime(1, '2018-01-01 12:00:00');
        $this->updateReportSubmissionByIdWithNewDateTime(2, '2018-01-31 12:00:00');
        self::fixtures()->flush();

        $data = $this->makeRequestAndReturnResults(
            '/report-submission/pre-registration-data',
            ['fromDate' => $fromDate, 'toDate' => $toDate]
        );

        $this->assertCount($expectedOutcomes['count'], $data);

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
                'fromDate' => '2018-01-01',
                'toDate' => '2018-01-31',
                'expectedOutcomes' => [
                    'count' => 2,
                    'caseNumbers' => ['1000000', '1000001'],
                ],
            ],
            [
                'fromDate' => '2017-12-31',
                'toDate' => '2018-02-01',
                'expectedOutcomes' => [
                    'count' => 2,
                    'caseNumbers' => ['1000000', '1000001'],
                ],
            ],
            [
                'fromDate' => '2018-01-02',
                'toDate' => '2018-01-31',
                'expectedOutcomes' => [
                    'count' => 1,
                    'caseNumbers' => ['1000001'],
                ],
            ],
            [
                'fromDate' => '2018-01-01',
                'toDate' => '2018-01-30',
                'expectedOutcomes' => [
                    'count' => 1,
                    'caseNumbers' => ['1000000'],
                ],
            ],
        ];
    }

    public function testGetCaserecDataRetrievesUpToNowIfNotGivenToDate()
    {
        $reportId = 1;
        $this->updateReportSubmissionByIdWithNewDateTime($reportId, 'today');
        self::fixtures()->flush();

        $result = $this->makeRequestAndReturnResults('/report-submission/pre-registration-data', []);
        $this->assertResponseIncludesReportWithCaseNumber($result, '1000000');
    }

    /**
     * @test
     */
    public function updatePersistsUuidWhenProvided()
    {
        $reportSubmission = (new ReportSubmissionHelper())->generateAndPersistReportSubmission(self::fixtures()->getEntityManager());

        $uuid = '5a8b1a26-8296-4373-ae61-f8d0b250e773';

        $url = sprintf('/report-submission/%s/update-uuid', $reportSubmission->getId());

        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['uuid' => $uuid],
        ]);

        $updatedSubmission = $this->makeRequestAndReturnResults('/report-submission/'.$reportSubmission->getId(), []);

        self::assertEquals($response['data'], $reportSubmission->getId());
        self::assertEquals($uuid, $updatedSubmission['uuid']);
    }

    /**
     * @throws \Exception
     */
    private function updateReportSubmissionByIdWithNewDateTime(int $id, string $date)
    {
        $entity = self::fixtures()->getRepo('Report\ReportSubmission')->findOneById($id);
        $entity->setCreatedOn(new \DateTime($date));

        self::fixtures()->persist($entity);
    }

    private function makeRequestAndReturnResults(string $endpoint, array $params)
    {
        $url = sprintf('%s?%s', $endpoint, http_build_query($params));

        $response = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        return $response['data'];
    }

    private function assertResponseIncludesReportWithCaseNumber(array $data, string $caseNumber)
    {
        $testPassed = false;
        foreach ($data as $row) {
            if ($row['case_number'] == $caseNumber) {
                $testPassed = true;
                break;
            }
        }

        $this->assertTrue($testPassed, sprintf('Response does not contain report for case number %s', $caseNumber));
    }

    public function testQueueDocumentsHasSuitablePermissions()
    {
        $url = '/report-submission/1/queue-documents';

        // assert Auth
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointAllowedFor('PUT', $url, self::$tokenSuperAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);
    }

    public function testQueueDocumentsQueuesValidRecords()
    {
        $documents = [
            ['null.pdf', null, true],
            ['QUEUED.pdf', Document::SYNC_STATUS_QUEUED, false],
            ['IN_PROGRESS.pdf', Document::SYNC_STATUS_IN_PROGRESS, false],
            ['SUCCESS.pdf', Document::SYNC_STATUS_SUCCESS, false],
            ['TEMPORARY_ERROR.pdf', Document::SYNC_STATUS_TEMPORARY_ERROR, true],
            ['PERMANENT_ERROR.pdf', Document::SYNC_STATUS_PERMANENT_ERROR, true],
        ];

        $user = self::fixtures()->createUser();
        $client = self::fixtures()->createClient($user);
        $report = self::fixtures()->createReport($client);
        $reportSubmission = new ReportSubmission($report, $user);
        self::fixtures()->persist($reportSubmission);

        foreach ($documents as $i => $document) {
            $record = self::fixtures()->createDocument($report, $document[0]);
            $record->setReportSubmission($reportSubmission);

            if (!is_null($document[1])) {
                $record->setSynchronisationStatus($document[1]);
            }
        }

        self::fixtures()->flush();
        self::fixtures()->clear();

        $this->assertJsonRequest('PUT', '/report-submission/'.$reportSubmission->getId().'/queue-documents', [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenSuperAdmin,
            'data' => [],
        ]);

        foreach ($documents as $document) {
            $record = self::fixtures()->getRepo('Report\Document')->findOneBy(['fileName' => $document[0]]);

            if ($document[2]) {
                self::assertEquals(Document::SYNC_STATUS_QUEUED, $record->getSynchronisationStatus());
                self::assertEquals('super_admin@example.org', $record->getSynchronisedBy()->getEmail());
            } else {
                self::assertEquals($document[1], $record->getSynchronisationStatus());
                self::assertEquals(null, $record->getSynchronisedBy());
            }
        }
    }

    public function testCannotQueueArchivedSubmissions()
    {
        $user = self::fixtures()->createUser();
        $client = self::fixtures()->createClient($user);
        $report = self::fixtures()->createReport($client);
        $reportSubmission = new ReportSubmission($report, $user);
        $reportSubmission->setArchived(true);
        self::fixtures()->persist($reportSubmission);

        self::fixtures()->flush();

        $this->assertJsonRequest('PUT', '/report-submission/'.$reportSubmission->getId().'/queue-documents', [
            'mustFail' => true,
            'assertResponseCode' => Response::HTTP_BAD_REQUEST,
            'AuthToken' => self::$tokenSuperAdmin,
            'data' => [],
        ]);
    }
}
