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
            ['data' => ['score' => 4, 'comments' => 'foo']],
            ['data' =>['reportType' => '102-5', 'comments' => 'foo']]
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
        $this->assertEquals($data['reportType'], $persistedEntity->getReportType());

        if (array_key_exists('comments', $data)) {
            $this->assertEquals($data['comments'], $persistedEntity->getComments());
        } else {
            $this->assertNull($persistedEntity->getComments());
        }
    }

    /**
     * @return array
     */
    public function getValidInputs()
    {
        return [
            ['data' => ['score' => 4, 'reportType' => 'foo', 'comments' => 'bar']],
            ['data' => ['score' => 4, 'reportType' => 'foo']]
        ];
    }
}
