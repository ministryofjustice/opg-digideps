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

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenProf = $this->loginAsProf();
            self::$tokenPa = $this->loginAsPa();
        }
    }

    public function testupdateNote()
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

        foreach (range(1, 5) as $score) {
            $this->assertJsonRequest('POST', $url, [
                'mustSucceed' => true,
                'AuthToken'   => self::$tokenDeputy,
                'data'        => [
                    'score'      => $score,
                    'reportType' => '102-6',
                ],
            ]);
        }
    }
}
