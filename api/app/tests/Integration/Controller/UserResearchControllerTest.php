<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Satisfaction;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;

class UserResearchControllerTest extends AbstractTestController
{
    private static $tokenAdmin;
    private static $tokenSuperAdmin;
    private static $tokenDeputy;
    private static $tokenProf;
    private static $tokenPa;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenSuperAdmin = $this->loginAsSuperAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenProf = $this->loginAsProf();
            self::$tokenPa = $this->loginAsPa();
        }
    }

    /** @test */
    public function userResearchHasSuitablePermissionsNotAllowedAdmin()
    {
        $satisfaction = $this->prepareSatisfaction();
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin, $validData);
    }

    /** @test */
    public function userResearchHasSuitablePermissionsNotAllowedSuperAdmin()
    {
        $satisfaction = $this->prepareSatisfaction();
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenSuperAdmin, $validData);
    }

    /** @test */
    public function userResearchHasSuitablePermissionsAllowedLay()
    {
        $satisfaction = $this->prepareSatisfaction();
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, self::$tokenDeputy, $validData);
    }

    /** @test */
    public function userResearchHasSuitablePermissionsAllowedPa()
    {
        $satisfaction = $this->prepareSatisfaction();
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, self::$tokenPa, $validData);
    }

    /** @test */
    public function userResearchHasSuitablePermissionsAllowedProf()
    {
        $satisfaction = $this->prepareSatisfaction();
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, self::$tokenProf, $validData);
    }

    /** @test */
    public function userResearchHasSuitablePermissionsNeedsAuthNoToken()
    {
        $url = '/user-research';
        $this->assertEndpointNeedsAuth('POST', $url);
    }

    private function prepareSatisfaction()
    {
        $em = static::getContainer()->get('em');

        $report = (new ReportTestHelper())->generateReport($em);
        $client = (new ClientTestHelper())->generateClient($em);

        $report->setClient($client);

        $satisfaction = (new Satisfaction())
            ->setReport($report)
            ->setScore(2);

        $em->persist($client);
        $em->persist($report);
        $em->persist($satisfaction);
        $em->flush();

        return $satisfaction;
    }

    /**
     * @dataProvider getInvalidInputs
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
                ],
            ],
            [
                [
                    'deputyshipLength' => 'underOne',
                    'agreedResearchTypes' => [],
                    'hasAccessToVideoCallDevice' => 'yes',
                ],
            ],
            [
                [
                    'deputyshipLength' => 'underOne',
                    'agreedResearchTypes' => ['surveys'],
                    'hasAccessToVideoCallDevice' => null,
                ],
            ],
        ];
    }
}
