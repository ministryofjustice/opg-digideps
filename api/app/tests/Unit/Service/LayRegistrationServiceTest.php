<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use stdClass;
use App\Entity\Client;
use App\Entity\Report\Report;
use App\Repository\ClientRepository;
use App\Service\LayRegistrationService;
use App\Service\ReportService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\isInstanceOf;

class LayRegistrationServiceTest extends TestCase
{
    private EntityManager&MockObject $mockEntityManager;
    private ClientRepository&MockObject $mockClientRepository;
    private ReportService&MockObject $mockReportService;
    private LayRegistrationService $sut;

    public function setUp(): void
    {
        $this->mockEntityManager = self::createMock(EntityManager::class);
        $this->mockClientRepository = self::createMock(ClientRepository::class);
        $this->mockReportService = self::createMock(ReportService::class);

        $this->sut = new LayRegistrationService(
            $this->mockEntityManager,
            $this->mockClientRepository,
            $this->mockReportService
        );
    }

    public function testAddMissingReports(): void
    {
        $mockClient1 = self::createMock(Client::class);
        $mockClient2 = self::createMock(Client::class);
        $mockClient3 = self::createMock(Client::class);
        $mockClients = [$mockClient1, $mockClient2, $mockClient3];

        $this->mockClientRepository->expects(self::once())
            ->method('findClientsWithoutAReport')
            ->willReturn($mockClients);

        $counter = new stdClass();
        $counter->current = 0;

        $this->mockReportService->expects(self::exactly(3))
            ->method('createRequiredReports')
            ->with(isInstanceOf(Client::class))
            ->willReturnCallback(function ($client) use ($mockClients, $counter) {
                static::assertEquals($mockClients[$counter->current], $client);

                ++$counter->current;

                return [self::createMock(Report::class)];
            });

        $this->mockEntityManager->expects(self::any())
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                self::assertTrue(is_a($entity, Report::class) || is_a($entity, Client::class));
            });

        $this->mockEntityManager->expects(self::any())
            ->method('flush');

        $this->mockEntityManager->expects(self::any())
            ->method('clear');

        $numReports = $this->sut->addMissingReports(batchSize: 2);

        self::assertEquals(3, $numReports);
    }
}
