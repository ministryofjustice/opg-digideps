<?php declare(strict_types=1);


namespace Tests\App\Controller;

use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use DateTime;

class StatsControllerTest extends AbstractTestController
{
    private $entityManager;

    public function setUp(): void
    {
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
            '/stats/activeLays',
            [
                'mustSucceed' => true,
                'AuthToken' => $this->loginAsSuperAdmin(),
            ]
        );

        self::assertIsArray($response);
    }
}
