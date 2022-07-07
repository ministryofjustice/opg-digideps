<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

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

    /** @test */
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

    /** @test */
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

    /** @test */
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
                '/stats/report/benefits_metrics',
                [
                    'mustFail' => true,
                    'AuthToken' => $token,
                ]
            );
        }
    }

    /** @test */
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
                '/stats/admins/old_report_data',
                [
                   'mustFail' => true,
                   'AuthToken' => $token,
                ]
            );
        }
    }
}
