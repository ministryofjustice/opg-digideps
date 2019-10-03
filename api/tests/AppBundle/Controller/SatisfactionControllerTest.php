<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Satisfaction;
use Symfony\Bridge\Doctrine\Tests\Fixtures\User;

class SatisfactionControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private static $tokenDeputy;
    private static $tokenProf;
    private static $tokenPa;

    public function setUp(): void
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenProf = $this->loginAsProf();
            self::$tokenPa = $this->loginAsPa();
        }
    }

    public function testSatisfactionHasSuitablePermissions()
    {
        $url = '/satisfaction';
        $okayData = [
            'score'      => 4,
            'reportType' => '103',
        ];

        // assert Auth
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin, $okayData);
        $this->assertEndpointAllowedFor('POST', $url, self::$tokenDeputy, $okayData);
        $this->assertEndpointAllowedFor('POST', $url, self::$tokenProf, $okayData);
        $this->assertEndpointAllowedFor('POST', $url, self::$tokenPa, $okayData);
    }

    public function testPublicEndpointHasSuitablePermissions()
    {
        $this->assertJsonRequest('POST', '/satisfaction/public', [
            'mustSucceed' => true,
            'data' => [
                'score' => 4
            ],
            'assertResponseCode' => 200,
        ]);
    }

    /**
     * @dataProvider getInvalidInputs
     * @param $data
     */
    public function testSatisfactionFailsOnInvalidData($data)
    {
        $this->assertJsonRequest('POST', '/satisfaction', [
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
            ['data' => ['reportType' => '102-5']]
        ];
    }

    /**
     * @dataProvider getValidInputs
     * @param $data
     */
    public function testSatisfactionAcceptsValidData($data)
    {
        $response = $this->assertJsonRequest('POST', '/satisfaction', [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => $data,
        ]);

        $em = self::$frameworkBundleClient->getContainer()->get('em');
        $persistedEntity = $em->getRepository('AppBundle\Entity\Satisfaction')->find($response['data']);

        $this->assertEquals($data['score'], $persistedEntity->getScore());

        if (array_key_exists('reportType', $data)) {
            $this->assertEquals($data['reportType'], $persistedEntity->getReportType());
            $this->assertEquals('ROLE_LAY_DEPUTY', $persistedEntity->getDeputyRole());
        } else {
            $this->assertNull($persistedEntity->getReportType());
            $this->assertNull($persistedEntity->getDeputyRole());
        }
    }

    /**
     * @return array
     */
    public function getValidInputs()
    {
        return [
            ['data' => ['score' => 4]],
            ['data' => ['score' => 4, 'reportType' => 'foo']]
        ];
    }
}
