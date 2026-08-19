<?php

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\Deputy\DeputyType;

class UserResearchControllerTest extends AbstractTestController
{
    public function testUserResearchHasSuitablePermissionsNotAllowedAdmin(): void
    {
        [$satisfaction] = $this->prepareSatisfaction(DeputyType::LAY);
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointNotAllowedFor('POST', $url, $this->loginAsAdmin(), $validData);
    }

    public function testUserResearchHasSuitablePermissionsNotAllowedSuperAdmin(): void
    {
        [$satisfaction] = $this->prepareSatisfaction(DeputyType::LAY);
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointNotAllowedFor('POST', $url, $this->loginAsSuperAdmin(), $validData);
    }

    public function testUserResearchHasSuitablePermissionsAllowedLay(): void
    {
        [$satisfaction, $email] = $this->prepareSatisfaction(DeputyType::LAY);
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, $this->loginAsDeputy($email), $validData);
    }

    public function testUserResearchHasSuitablePermissionsAllowedPa(): void
    {
        [$satisfaction, $email] = $this->prepareSatisfaction(DeputyType::PA);
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, $this->loginAsDeputy($email), $validData);
    }

    public function testUserResearchHasSuitablePermissionsAllowedProf(): void
    {
        [$satisfaction, $email] = $this->prepareSatisfaction(DeputyType::PRO);
        $url = '/user-research';
        $validData = [
            'deputyshipLength' => 'underOne',
            'agreedResearchTypes' => ['surveys', 'videoCall', 'phone'],
            'hasAccessToVideoCallDevice' => 'yes',
            'satisfaction' => $satisfaction->getId(),
        ];

        $this->assertEndpointAllowedFor('POST', $url, $this->loginAsDeputy($email), $validData);
    }

    public function testUserResearchHasSuitablePermissionsNeedsAuthNoToken(): void
    {
        $url = '/user-research';
        $this->assertEndpointNeedsAuth('POST', $url);
    }

    /**
     * @return array{Satisfaction, string}
     */
    private function prepareSatisfaction(DeputyType $deputyType): array
    {
        ['persons' => ['users' => ['deputy' => $user]], 'orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(
            new Scenario(new CourtOrderDescriptor(new DeputySet(new DeputyDescriptor('deputy', $deputyType))))
        );

        $satisfaction = new Satisfaction(2)->setReport($report);
        self::$fixtureService->persist($satisfaction);
        self::$fixtureService->flush();

        return [$satisfaction, $user->getEmail()];
    }

    /**
     * @dataProvider getInvalidInputs
     */
    public function testUserResearchFailsOnInvalidData($data): void
    {
        $this->assertJsonRequest('POST', '/user-research', [
            'mustFail' => true,
            'AuthToken' => $this->loginAsDeputy(),
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
