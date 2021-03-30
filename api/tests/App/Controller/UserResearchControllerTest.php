<?php


namespace Tests\App\Controller;

class UserResearchControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private static $tokenSuperAdmin;
    private static $tokenDeputy;
    private static $tokenProf;
    private static $tokenPa;

    public function setUp(): void
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenSuperAdmin = $this->loginAsSuperAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenProf = $this->loginAsProf();
            self::$tokenPa = $this->loginAsPa();
        }
    }

    /** @test */
    public function userResearchHasSuitablePermissions()
    {
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
        ];

        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin, $validData);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenSuperAdmin, $validData);
        $this->assertEndpointAllowedFor('POST', $url, self::$tokenDeputy, $validData);
        $this->assertEndpointAllowedFor('POST', $url, self::$tokenProf, $validData);
        $this->assertEndpointAllowedFor('POST', $url, self::$tokenPa, $validData);
    }

    /**
     * @dataProvider getInvalidInputs
     * @param $data
     */
    public function testUserResearchFailsOnInvalidData($data)
    {
        $this->assertJsonRequest('POST', '/user-research', [
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
            [
                [
                    'deputyshipLength' => null,
                    'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
                    'hasAccessToVideoCallDevice' => 'yes',
                ]
            ],
            [
                [
                    'deputyshipLength' => 'underOne',
                    'agreedResearchTypes' => [],
                    'hasAccessToVideoCallDevice' => 'yes',
                ]
            ],
            [
                [
                    'deputyshipLength' => 'underOne',
                    'agreedResearchTypes' => ['surveys'],
                    'hasAccessToVideoCallDevice' => null,
                ]
            ],
        ];
    }
}
