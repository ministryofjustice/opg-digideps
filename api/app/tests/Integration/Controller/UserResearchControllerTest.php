<?php

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\TestHelpers\ClientTestHelper;
use OPG\Digideps\Backend\TestHelpers\ReportTestHelper;

class UserResearchControllerTest extends AbstractTestController
{
    private static ?string $tokenAdmin = null;
    private static string $tokenSuperAdmin;
    private static string $tokenDeputy;
    private static string $tokenProf;
    private static string $tokenPa;

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

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testUserResearchHasSuitablePermissionsNotAllowedAdmin(): void
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

    public function testUserResearchHasSuitablePermissionsNotAllowedSuperAdmin(): void
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

    public function testUserResearchHasSuitablePermissionsAllowedLay(): void
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

    public function testUserResearchHasSuitablePermissionsAllowedPa(): void
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

    public function testUserResearchHasSuitablePermissionsAllowedProf(): void
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

    public function testUserResearchHasSuitablePermissionsNeedsAuthNoToken(): void
    {
        $url = '/user-research';
        $this->assertEndpointNeedsAuth('POST', $url);
    }

    private function prepareSatisfaction(): Satisfaction
    {
        $em = static::getContainer()->get('em');

        $report = ReportTestHelper::create()->generateReport($em);
        $client = ClientTestHelper::create()->generateClient($em);

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
    public function testUserResearchFailsOnInvalidData($data): void
    {
        $this->assertJsonRequest('POST', '/user-research', [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => $data,
        ]);
    }

    public static function getInvalidInputs(): array
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
