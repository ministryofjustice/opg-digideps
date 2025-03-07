<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Repository\ClientRepository;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\Uploader\ClientMatch;
use App\v2\Registration\Uploader\LayClientMatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class LayClientMatcherTest extends TestCase
{
    private EntityManagerInterface $em;
    private LayClientMatcher $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->sut = new LayClientMatcher($this->em);
    }

    public function testMatchDtoWithMatchingClientAndReport(): void
    {
        $dto = new LayDeputyshipDto();
        $dto->setCaseNumber('1234567T');
        $dto->setTypeOfReport('OPG102');
        $dto->setOrderType('pfa');
        $dto->setHybrid(null);

        $client = new Client();
        $report = new Report($client, '102', new \DateTime(), new \DateTime(), false);
        $client->addReport($report);

        $clientRepository = $this->createMock(ClientRepository::class);
        $clientRepository->expects($this->once())
            ->method('findByCaseNumberIncludingDischarged')
            ->with('1234567T')
            ->willReturn([$client]);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Client::class)
            ->willReturn($clientRepository);

        $expectedMatch = new ClientMatch($client, $report, null, true);
        $actualMatch = $this->sut->matchDto($dto);

        $this->assertEquals($expectedMatch, $actualMatch);
    }

    public function testMatchDtoWithNoMatchingClient(): void
    {
        $dto = new LayDeputyshipDto();
        $dto->setCaseNumber('1234567T');

        $clientRepository = $this->createMock(ClientRepository::class);
        $clientRepository->expects($this->once())
            ->method('findByCaseNumberIncludingDischarged')
            ->with('1234567T')
            ->willReturn([]);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Client::class)
            ->willReturn($clientRepository);

        $expectedMatch = new ClientMatch(null, null, null, false);
        $actualMatch = $this->sut->matchDto($dto);

        $this->assertEquals($expectedMatch, $actualMatch);
    }

    public function testMatchDtoWithDeletedClient(): void
    {
        $dto = new LayDeputyshipDto();
        $dto->setCaseNumber('1234567T');

        $client = new Client();
        $client->setDeletedAt(new \DateTime());

        $clientRepository = $this->createMock(ClientRepository::class);
        $clientRepository->expects($this->once())
            ->method('findByCaseNumberIncludingDischarged')
            ->with('1234567T')
            ->willReturn([$client]);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Client::class)
            ->willReturn($clientRepository);

        $expectedMatch = new ClientMatch(null, null, null, false);
        $actualMatch = $this->sut->matchDto($dto);

        $this->assertEquals($expectedMatch, $actualMatch);
    }

    public function testMatchDtoWithIncompatibleReport(): void
    {
        $dto = new LayDeputyshipDto();
        $dto->setCaseNumber('1234567T');
        $dto->setTypeOfReport('OPG103'); // Incompatible report type
        $dto->setOrderType('pfa');
        $dto->setHybrid(null);

        $client = new Client();
        $report = new Report($client, '102', new \DateTime(), new \DateTime(), false);
        $client->addReport($report);

        $clientRepository = $this->createMock(ClientRepository::class);
        $clientRepository->expects($this->once())
            ->method('findByCaseNumberIncludingDischarged')
            ->with('1234567T')
            ->willReturn([$client]);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Client::class)
            ->willReturn($clientRepository);

        $expectedMatch = new ClientMatch(null, null, null, true);
        $actualMatch = $this->sut->matchDto($dto);

        $this->assertEquals($expectedMatch, $actualMatch);
    }

    public function testMatchDtoWithHybridReportTypeChange(): void
    {
        $dto = new LayDeputyshipDto();
        $dto->setCaseNumber('1234567T');
        $dto->setTypeOfReport('OPG102');
        $dto->setOrderType('hw');
        $dto->setHybrid('HYBRID');

        $client = new Client();
        $report = new Report($client, '102', new \DateTime(), new \DateTime(), false);
        $client->addReport($report);

        $clientRepository = $this->createMock(ClientRepository::class);
        $clientRepository->expects($this->once())
            ->method('findByCaseNumberIncludingDischarged')
            ->with('1234567T')
            ->willReturn([$client]);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Client::class)
            ->willReturn($clientRepository);

        $expectedMatch = new ClientMatch($client, $report, '102-4', true);

        $actualMatch = $this->sut->matchDto($dto);

        $this->assertEquals($expectedMatch, $actualMatch);
    }
}
