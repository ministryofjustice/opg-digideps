<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\ReportList;
use OPG\Digideps\Backend\Fixture\Scenario;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;
use Symfony\Component\HttpFoundation\Response;
use Tests\OPG\Digideps\Backend\Integration\Fixtures;

class ReportSubmissionControllerTest extends AbstractTestController
{
    public function testGetAllWithFiltersGetOneArchive()
    {
        self::$em->clear();
        Fixtures::deleteReportsData();
        ['orders' => [['pfa' => ['reports' => [$report0]]]], 'persons' => $persons] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneNamedPa(), reportList: ReportList::manyReports())));
        $report0->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        $caseNumber0 = $report0->getClient()->getCaseNumber() ?? '';
        ['orders' => [['pfa' => ['reports' => [$report1]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneNamedPa(), reportList: ReportList::manyReports())), $persons);
        $report1->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        $caseNumber1 = $report1->getClient()->getCaseNumber() ?? '';
        ['orders' => [['pfa' => ['reports' => [$report2]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneNamedPa(), reportList: ReportList::manyReports())), $persons);
        $submission2 = $report2->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        $caseNumber2 = $report2->getClient()->getCaseNumber() ?? '';
        self::$fixtureService->persist(new Document($report2, 'file1.pdf')
            ->setStorageReference('storageref1')
            ->setReportSubmission($submission2)
            ->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED));
        ['orders' => [['pfa' => ['reports' => [$report3]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())), $persons);
        $report3->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        $caseNumber3 = $report3->getClient()->getCaseNumber() ?? '';
        ['orders' => [['pfa' => ['reports' => [$report4]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())), $persons);
        $caseNumber4 = $report4->getClient()->getCaseNumber() ?? '';
        $submission4 = $report4->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        self::$fixtureService->persist(new Document($report4, 'file1.pdf')
            ->setStorageReference('storageref1')
            ->setReportSubmission($submission4));
        $deputyUserName = $persons['users']['lay1']->getLastName();
        $persons['users']['pa1']->setLastName($deputyUserName);
        self::$fixtureService->flush();

        $tokenAdmin = $this->loginAsAdmin();
        $reportsGetAllRequest = function (array $params = []) use ($tokenAdmin): array {
            $url = '/report-submission?' . http_build_query($params);

            /**
             * @var array<array<array>> $data
             */
            $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => $tokenAdmin,
            ])['data'];
            return $data;
        };

        $this->assertEndpointNeedsAuth('GET', '/report-submission');
        $this->assertEndpointNotAllowedFor('GET', '/report-submission', $this->loginAsDeputy());

        // assert submission (only one expected)
        $data = $reportsGetAllRequest(['status' => 'new']);
        $this->assertEquals(['new' => 4, 'pending' => 1, 'archived' => 0], $data['counts']);

        $submission4 = $this->getSubmissionByCaseNumber($data['records'], $caseNumber4);
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
            'AuthToken' => $tokenAdmin,
        ])['data'];
        $this->assertEquals($submission4['id'], $data['id']);
        $this->assertEquals('storageref1', $data['documents'][0]['storage_reference']);

        // archive 1st submission
        $data = $this->assertJsonRequest('PUT', '/report-submission/' . $submission4['id'], [
            'mustSucceed' => true,
            'AuthToken' => $tokenAdmin,
            'data' => ['archive' => true],
        ])['data'];
        $this->assertEquals($submission4['id'], $data);

        // check counts after submission
        $data = $reportsGetAllRequest([]);
        $this->assertEquals(['new' => 3, 'pending' => 1, 'archived' => 1], $data['counts']);
        $this->assertCount(5, $data['records']);

        // check filters and counts
        $data = $reportsGetAllRequest(['q' => $caseNumber1]);
        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $data = $reportsGetAllRequest(['q' => $caseNumber1, 'status' => 'new']);
        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $data = $reportsGetAllRequest(['q' => $caseNumber2, 'status' => 'new']);
        $this->assertEquals(['new' => 0, 'pending' => 1, 'archived' => 0], $data['counts']);
        $this->assertCount(0, $data['records']);

        $data = $reportsGetAllRequest(['q' => $caseNumber2, 'status' => 'pending']);
        $this->assertEquals(['new' => 0, 'pending' => 1, 'archived' => 0], $data['counts']);
        $this->assertCount(1, $data['records']);

        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 0], $reportsGetAllRequest(['status' => 'new', 'q' => $report0->getClient()->getFirstName()])['counts']); // client name
        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 0], $reportsGetAllRequest(['status' => 'new', 'q' => $report0->getClient()->getLastName()])['counts']); // client surname
        $this->assertEquals(['new' => 3, 'pending' => 1, 'archived' => 1], $reportsGetAllRequest(['status' => 'new', 'q' => $deputyUserName])['counts']); // deputy name
        $this->assertEquals(['new' => 1, 'pending' => 0, 'archived' => 1], $reportsGetAllRequest(['created_by_role' => 'ROLE_LAY_DEPUTY'])['counts']);
        // since this filter works with the role being a prefix, ROLE_PA would include all the ROLE_PA* ones
        // a better version would calculate all the inheritance
        $this->assertEquals(['new' => 2, 'pending' => 1, 'archived' => 0], $reportsGetAllRequest(['created_by_role' => 'ROLE_PA'])['counts']);

        // check pagination and limit
        $submissions = $reportsGetAllRequest(['status' => 'new', 'q' => $deputyUserName])['records'];
        $this->assertEquals([$caseNumber0, $caseNumber1, $caseNumber3], $this->getOrderedCaseNumbersFromSubmissions($submissions));

        $submissions = $reportsGetAllRequest(['status' => 'new', 'q' => $deputyUserName, 'orderBy' => 'id', 'limit' => 2, 'offset' => 1])['records'];
        $this->assertEquals([$caseNumber0, $caseNumber1], $this->getOrderedCaseNumbersFromSubmissions($submissions));
    }

    private function getOrderedCaseNumbersFromSubmissions($submissions): array
    {
        $ret = array_map(function ($submission) {
            return $submission['report']['client']['case_number'];
        }, $submissions);

        sort($ret);

        return $ret;
    }

    /**
     * @param array<array> $submissions
     */
    private function getSubmissionByCaseNumber(array $submissions, string $caseNumber): array
    {
        $ret = array_filter($submissions, function (array $submission) use ($caseNumber) {
            return $submission['report']['client']['case_number'] === $caseNumber;
        });

        return array_shift($ret) ?? throw new \LogicException("This cannot be nothing");
    }

    #[DataProvider('getDateRangeThresholds')]
    public function testGetCaserecDataRetrievesWithinGivenDateRangesInclusive(string $fromDate, string $toDate, array $expectedOutcomes)
    {
        self::$em->clear();
        Fixtures::deleteReportsData();
        ['client' => $client1, 'persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [$report1]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::oneUnsubmittedReport())));
        self::$fixtureService->persist(new ReportSubmission($report1, $user1))->setCreatedOn(new \DateTime('2018-01-01 12:00:00'));
        $report1->setSubmitted(true);
        $report1->setSubmitDate(new \DateTime('2018-01-01 00:00:00'));
        ['client' => $client2, 'persons' => ['users' => ['lay1' => $user2]], 'orders' => [['pfa' => ['reports' => [$report2]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::oneUnsubmittedReport())));
        self::$fixtureService->persist(new ReportSubmission($report2, $user2))->setCreatedOn(new \DateTime('2018-01-31 12:00:00'));
        $report2->setSubmitted(true);
        $report2->setSubmitDate(new \DateTime('2018-01-31 00:00:00'));
        self::$fixtureService->flush();

        $data = $this->makeRequestAndReturnResults(
            '/report-submission/pre-registration-data',
            ['fromDate' => $fromDate, 'toDate' => $toDate]
        );

        $caseNumbers = [$client1->getCaseNumber() ?? '', $client2->getCaseNumber() ?? ''];

        $this->assertCount($expectedOutcomes['count'], $data);

        /**
         * @var string $expectedCaseNumber
         */
        foreach ($expectedOutcomes['caseNumbers'] as $expectedCaseNumber) {
            $this->assertResponseIncludesReportWithCaseNumber($data, $caseNumbers[$expectedCaseNumber]);
        }
    }

    public static function getDateRangeThresholds(): array
    {
        return [
            [
                'fromDate' => '2018-01-01',
                'toDate' => '2018-01-31',
                'expectedOutcomes' => [
                    'count' => 2,
                    'caseNumbers' => [0, 1],
                ],
            ],
            [
                'fromDate' => '2017-12-31',
                'toDate' => '2018-02-01',
                'expectedOutcomes' => [
                    'count' => 2,
                    'caseNumbers' => [0, 1],
                ],
            ],
            [
                'fromDate' => '2018-01-02',
                'toDate' => '2018-01-31',
                'expectedOutcomes' => [
                    'count' => 1,
                    'caseNumbers' => [1],
                ],
            ],
            [
                'fromDate' => '2018-01-01',
                'toDate' => '2018-01-30',
                'expectedOutcomes' => [
                    'count' => 1,
                    'caseNumbers' => [0],
                ],
            ],
        ];
    }

    public function testGetCaserecDataRetrievesUpToNowIfNotGivenToDate()
    {
        ['client' => $client, 'orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())));
        $reportSubmission = self::$fixtureService->persist($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"));
        $reportSubmission->setCreatedOn(new \DateTime('today'));
        self::$fixtureService->flush();

        $result = $this->makeRequestAndReturnResults('/report-submission/pre-registration-data', []);
        $this->assertResponseIncludesReportWithCaseNumber($result, $client->getCaseNumber() ?? '');
    }

    public function testUpdatePersistsUuidWhenProvided(): void
    {
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())));
        $reportSubmission = $report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");

        $uuid = '5a8b1a26-8296-4373-ae61-f8d0b250e773';

        $url = sprintf('/report-submission/%s/update-uuid', $reportSubmission->getId());

        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'ClientSecret' => API_TOKEN_DEPUTY,
            'data' => ['uuid' => $uuid],
        ]);

        $updatedSubmission = $this->makeRequestAndReturnResults('/report-submission/' . $reportSubmission->getId(), []);

        self::assertEquals($response['data'], $reportSubmission->getId());
        self::assertEquals($uuid, $updatedSubmission['uuid']);
    }

    private function makeRequestAndReturnResults(string $endpoint, array $params): array
    {
        $url = sprintf('%s?%s', $endpoint, http_build_query($params));

        /**
         * @var array{data: array} $response
         */
        $response = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => $this->loginAsAdmin(),
        ]);

        return $response['data'];
    }

    private function assertResponseIncludesReportWithCaseNumber(array $data, string $caseNumber): void
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
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())));
        $reportSubmission = $report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        $url = "/report-submission/{$reportSubmission->getId()}/queue-documents";

        // assert Auth
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointAllowedFor('PUT', $url, $this->loginAsSuperAdmin());
        $this->assertEndpointNotAllowedFor('PUT', $url, $this->loginAsAdmin());
        $this->assertEndpointNotAllowedFor('PUT', $url, $this->loginAsDeputy());
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

        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())));
        $reportSubmission = $report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");

        foreach ($documents as $document) {
            $record = self::fixtures()->createDocument($report, $document[0]);
            $record->setReportSubmission($reportSubmission);

            if (!is_null($document[1])) {
                $record->setSynchronisationStatus($document[1]);
            }
        }

        self::$fixtureService->flush();
        self::$em->clear();

        $this->assertJsonRequest('PUT', '/report-submission/' . $reportSubmission->getId() . '/queue-documents', [
            'mustSucceed' => true,
            'AuthToken' => $this->loginAsSuperAdmin(),
            'data' => [],
        ]);

        foreach ($documents as $document) {
            $record = self::fixtures()->getRepo(Document::class)->findOneBy(['fileName' => $document[0]]);

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
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())));
        $reportSubmission = $report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        self::$fixtureService->persist($reportSubmission->setArchived(true));
        self::$fixtureService->flush();

        $this->assertJsonRequest('PUT', '/report-submission/' . $reportSubmission->getId() . '/queue-documents', [
            'mustFail' => true,
            'assertResponseCode' => Response::HTTP_BAD_REQUEST,
            'AuthToken' => $this->loginAsSuperAdmin(),
            'data' => [],
        ]);
    }
}
