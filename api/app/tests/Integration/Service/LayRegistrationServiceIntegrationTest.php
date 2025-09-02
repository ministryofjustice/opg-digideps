<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Service\LayRegistrationService;
use App\Tests\Integration\ApiBaseTestCase;

class LayRegistrationServiceIntegrationTest extends ApiBaseTestCase
{
    private LayRegistrationService $sut;

    public function setUp(): void
    {
        parent::setUp();

        /** @var LayRegistrationService $sut */
        $sut = $this->container->get(LayRegistrationService::class);
        $this->sut = $sut;
    }

    public function testAddMissingReports(): void
    {
        $reportsAdded = $this->sut->addMissingReports();
        self::assertEquals(0, $reportsAdded);
    }
}
