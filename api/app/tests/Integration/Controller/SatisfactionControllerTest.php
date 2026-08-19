<?php

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Common\Deputy\DeputyType;

class SatisfactionControllerTest extends AbstractTestController
{
    public function testSatisfactionHasSuitablePermissionsAllowedDeputy()
    {
        [$report, $email] = $this->prepareReport(DeputyType::LAY);

        $url = '/satisfaction';
        $okayData = [
            'score' => 4,
            'reportType' => '103',
            'comments' => 'a comment',
            'reportId' => $report->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, $this->loginAsDeputy($email), $okayData);
    }

    public function testSatisfactionHasSuitablePermissionsAllowedProf()
    {
        [$report, $email] = $this->prepareReport(DeputyType::PRO);

        $url = '/satisfaction';
        $okayData = [
            'score' => 4,
            'reportType' => '103',
            'comments' => 'a comment',
            'reportId' => $report->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, $this->loginAsDeputy($email), $okayData);
    }

    public function testSatisfactionHasSuitablePermissionsAllowedPa()
    {
        [$report, $email] = $this->prepareReport(DeputyType::PA);

        $url = '/satisfaction';
        $okayData = [
            'score' => 4,
            'reportType' => '103',
            'comments' => 'a comment',
            'reportId' => $report->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, $this->loginAsDeputy($email), $okayData);
    }

    public function testSatisfactionHasSuitablePermissionsNotAllowed()
    {
        [$report] = $this->prepareReport(DeputyType::LAY);

        $url = '/satisfaction';
        $okayData = [
            'score' => 4,
            'reportType' => '103',
            'comments' => 'a comment',
            'reportId' => $report->getId(),
        ];

        $this->assertEndpointNotAllowedFor('POST', $url, $this->loginAsAdmin(), $okayData);
    }

    /**
     * @return array{Report, string}
     */
    private function prepareReport(DeputyType $deputyType): array
    {
        ['persons' => ['users' => ['deputy' => $user]], 'orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(
            new Scenario(new CourtOrderDescriptor(new DeputySet(new DeputyDescriptor('deputy', $deputyType))))
        );
        return [$report, $user->getEmail()];
    }

    public function testSatisfactionHasSuitablePermissionsNoToken()
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
                'comments' => 'a comment',
            ],
            'assertResponseCode' => 200,
        ]);
    }

    /**
     * @dataProvider getInvalidInputs
     */
    public function testSatisfactionFailsOnInvalidData($url, $data)
    {
        $this->assertJsonRequest('POST', $url, [
            'mustFail' => true,
            'AuthToken' => $this->loginAsDeputy(),
            'data' => $data,
        ]);
    }

    public static function getInvalidInputs(): array
    {
        return [
            ['url' => '/satisfaction', 'data' => ['score' => 1]],
            ['url' => '/satisfaction', 'data' => ['reportType' => '102-5']],
            ['url' => '/satisfaction/public', 'data' => []],
        ];
    }

    /**
     * @dataProvider getValidInputs
     */
    public function testSatisfactionAcceptsValidData($url, $data)
    {
        [$report, $email] = $this->prepareReport(DeputyType::LAY);
        $data['reportId'] = $report->getId();

        $response = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => $this->loginAsDeputy($email),
            'data' => $data,
        ]);

        $persistedEntity = self::fixtures()->getRepo(Satisfaction::class)->find($response['data']);

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

    public static function getValidInputs(): array
    {
        return [
            ['url' => '/satisfaction', 'data' => ['score' => 4, 'reportType' => 'foo', 'comments' => 'a comment']],
            ['url' => '/satisfaction/public', 'data' => ['score' => 4, 'comments' => 'a comment']],
        ];
    }
}
