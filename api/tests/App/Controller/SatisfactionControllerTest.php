<?php

namespace Tests\App\Controller;

use App\Entity\Report\Report;
use App\Entity\Satisfaction;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use Symfony\Bridge\Doctrine\Tests\Fixtures\User;

class SatisfactionControllerTest extends AbstractTestController
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

    public function testSatisfactionHasSuitablePermissions_allowed_deputy()
    {
        $report = $this->prepareReport();

        $url = '/satisfaction';
        $okayData = [
            'score'      => 4,
            'reportType' => '103',
            'comments' => 'a comment',
            'reportId' => $report->getId()
        ];

        $this->assertEndpointAllowedFor('POST', $url, self::$tokenDeputy, $okayData);
    }

    public function testSatisfactionHasSuitablePermissions_allowed_prof()
    {
        $report = $this->prepareReport();

        $url = '/satisfaction';
        $okayData = [
            'score'      => 4,
            'reportType' => '103',
            'comments' => 'a comment',
            'reportId' => $report->getId()
        ];

        $this->assertEndpointAllowedFor('POST', $url, self::$tokenProf, $okayData);
    }

    public function testSatisfactionHasSuitablePermissions_allowed_pa()
    {
        $report = $this->prepareReport();

        $url = '/satisfaction';
        $okayData = [
            'score'      => 4,
            'reportType' => '103',
            'comments' => 'a comment',
            'reportId' => $report->getId()
        ];

        $this->assertEndpointAllowedFor('POST', $url, self::$tokenPa, $okayData);
    }

    public function testSatisfactionHasSuitablePermissions_not_allowed()
    {
        $report = $this->prepareReport();

        $url = '/satisfaction';
        $okayData = [
            'score'      => 4,
            'reportType' => '103',
            'comments' => 'a comment',
            'reportId' => $report->getId()
        ];

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin, $okayData);
    }

    private function prepareReport()
    {
        $reportTestHelper = new ReportTestHelper();
        $em = self::$container->get('em');

        $report = $reportTestHelper->generateReport($em);
        $client = (new ClientTestHelper())->generateClient($em);

        $report->setClient($client);

        $em->persist($client);
        $em->persist($report);
        $em->flush();

        return $report;
    }

    public function testSatisfactionHasSuitablePermissions_no_token()
    {
        $url = '/satisfaction';
        $this->assertEndpointNeedsAuth('POST', $url);
    }

    public function testPublicEndpointHasSuitablePermissions()
    {
        $this->assertJsonRequest('POST', '/satisfaction/public', [
            'mustSucceed' => true,
            'data' => [
                'score' => 4,
                'comments' => 'a comment'
            ],
            'assertResponseCode' => 200,
        ]);
    }

    /**
     * @dataProvider getInvalidInputs
     * @param $data
     */
    public function testSatisfactionFailsOnInvalidData($url, $data)
    {
        $this->assertJsonRequest('POST', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => $data,
        ]);
    }

    /**
     * @return array
     */
    public function getInvalidInputs()
    {
        return [
            ['url' => '/satisfaction', 'data' => ['score' => 1]],
            ['url' => '/satisfaction', 'data' => ['reportType' => '102-5']],
            ['url' => '/satisfaction/public', 'data' => []],
        ];
    }

    /**
     * @dataProvider getValidInputs
     * @param $data
     */
    public function testSatisfactionAcceptsValidData($url, $data)
    {
        $report = $this->prepareReport();
        $data['reportId'] = $report->getId();

        $response = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => $data,
        ]);

        $persistedEntity = self::fixtures()->getRepo('App\Entity\Satisfaction')->find($response['data']);

        $this->assertEquals($data['score'], $persistedEntity->getScore());

        if (array_key_exists('reportType', $data)) {
            $this->assertEquals($data['reportType'], $persistedEntity->getReportType());
        } else {
            $this->assertNull($persistedEntity->getReportType());
        }

        if ($url === '/satisfaction') {
            $this->assertEquals('ROLE_LAY_DEPUTY', $persistedEntity->getDeputyRole());
        } else {
            $this->assertNull($persistedEntity->getDeputyRole());
        }
    }

    /**
     * @return array
     */
    public function getValidInputs()
    {
        return [
            ['url' => '/satisfaction', 'data' => ['score' => 4, 'reportType' => 'foo', 'comments' => 'a comment']],
            ['url' => '/satisfaction/public', 'data' => ['score' => 4, 'comments' => 'a comment']],
        ];
    }
}
