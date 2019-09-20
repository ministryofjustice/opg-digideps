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

    public function testSatisfactionFailsOnInvalidData()
    {
        $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => false,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'score' => 4,
            ],
        ]);

        $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => false,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'reportType' => '102-5',
            ],
        ]);

        $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => false,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'score'      => 4,
                'reportType' => 'incorrect',
            ],
        ]);
    }

    public function testSatisfactionAcceptsValidData()
    {
        $scores = range(1, 5);
        $reportTypes = [
            'ndr',
            '102', '103', '104', '102-4', '103-4',
            '102-5', '103-5', '104-5', '102-4-5', '103-4-5',
            '102-6', '103-6', '104-6', '102-4-6', '103-4-6'
        ];

        foreach ($scores as $score) {
            foreach ($reportTypes as $reportType) {
                $this->assertJsonRequest('POST', $url, [
                    'mustSucceed' => true,
                    'AuthToken'   => self::$tokenDeputy,
                    'data'        => [
                        'score'      => $score,
                        'reportType' => $reportType,
                    ],
                ]);
            }
        }

    }
}
