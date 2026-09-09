<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use PHPUnit\Framework\Attributes\Test;

class StatsControllerTest extends AbstractTestController
{
    private $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    #[Test]
    public function activeLayDeputies()
    {
        $response = $this->assertJsonRequest(
            'GET',
            '/stats/deputies/lay/active',
            [
                'mustSucceed' => true,
                'AuthToken' => $this->loginAsSuperAdmin(),
            ],
            true
        );

        self::assertIsArray($response);
    }

    #[Test]
    public function activeLayDeputiesOnlySuperAdminsCanAccess()
    {
        $unauthorisedUserTokens = [
            $this->loginAsAdmin(),
            $this->loginAsDeputy(),
            $this->loginAsProf(),
            $this->loginAsPa(),
        ];

        foreach ($unauthorisedUserTokens as $token) {
            $this->assertJsonRequest(
                'GET',
                '/stats/deputies/lay/active',
                [
                    'mustFail' => true,
                    'AuthToken' => $token,
                ]
            );
        }
    }

    #[Test]
    public function benefitsReportMetricsOnlySuperAdminsCanAccess()
    {
        $unauthorisedUserTokens = [
            $this->loginAsAdmin(),
            $this->loginAsDeputy(),
            $this->loginAsProf(),
            $this->loginAsPa(),
        ];

        foreach ($unauthorisedUserTokens as $token) {
            $this->assertJsonRequest(
                'GET',
                '/stats/report/benefits-report-metrics',
                [
                    'mustFail' => true,
                    'AuthToken' => $token,
                ]
            );
        }
    }

    #[Test]
    public function oldAdminUsersOnlySuperAdminsCanAccess()
    {
        $unauthorisedUserTokens = [
            $this->loginAsAdmin(),
            $this->loginAsDeputy(),
            $this->loginAsProf(),
            $this->loginAsPa(),
        ];

        foreach ($unauthorisedUserTokens as $token) {
            $this->assertJsonRequest(
                'GET',
                '/stats/admins/inactive_admin_users',
                [
                    'mustFail' => true,
                    'AuthToken' => $token,
                ]
            );
        }
    }

    #[Test]
    public function imbalanceReportOnlySuperAdminsCanAccess()
    {
        $unauthorisedUserTokens = [
            $this->loginAsAdmin(),
            $this->loginAsDeputy(),
            $this->loginAsProf(),
            $this->loginAsPa(),
        ];

        foreach ($unauthorisedUserTokens as $token) {
            $this->assertJsonRequest(
                'GET',
                '/stats/report/imbalance',
                [
                    'mustFail' => true,
                    'AuthToken' => $token,
                ]
            );
        }
    }
}
