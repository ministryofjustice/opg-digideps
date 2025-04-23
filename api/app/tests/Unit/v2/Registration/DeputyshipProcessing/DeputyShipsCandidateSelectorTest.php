<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\Factory\StagingSelectedCandidateFactory;
use App\Repository\ClientRepository;
use App\Repository\DeputyRepository;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class DeputyShipsCandidateSelectorTest extends TestCase
{
    public function setUp(): void
    {
        $this->mockEntityManager = $this->createMock(EntityManager::class);
        $this->mockDeputyRepository = $this->createMock(DeputyRepository::class);
        $this->mockClientRepository = $this->createMock(ClientRepository::class);
        $this->mockStagingSelectedCandidateFactory = $this->createMock(StagingSelectedCandidateFactory::class);

        $this->sut = new DeputyshipsCandidatesSelector(
            $this->mockEntityManager,
            $this->mockDeputyRepository,
            $this->mockClientRepository,
            $this->mockStagingSelectedCandidateFactory
        );
    }
}
