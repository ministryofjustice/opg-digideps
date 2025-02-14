<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\Uploader;

use App\Repository\UserRepository;
use App\v2\Assembler\ClientAssembler;
use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\Uploader\LayCSVRowProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LayCSVRowProcessorTest extends TestCase
{
    private EntityManagerInterface $mockEm;
    private SiriusToLayDeputyshipDtoAssembler $mockLayDeputyAssembler;
    private ClientAssembler $mockClientAssembler;
    private LoggerInterface $mockLogger;
    private UserRepository $mockUserRepository;
    private LayCSVRowProcessor $sut;

    public function setUp(): void
    {
        $this->mockEm = $this->createMock(EntityManagerInterface::class);
        $this->mockLayDeputyAssembler = $this->createMock(SiriusToLayDeputyshipDtoAssembler::class);
        $this->mockClientAssembler = $this->createMock(ClientAssembler::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockUserRepository = $this->createMock(UserRepository::class);

        $this->sut = new LayCSVRowProcessor(
            $this->mockEm,
            $this->mockLayDeputyAssembler,
            $this->mockClientAssembler,
            $this->mockLogger
        );
    }

    /*
     * Unable to find the user to associate the client with -> error
     */
    public function testProcessRowNoUserException()
    {
        // Expectations
        $row = ['DeputyUid' => '111111111'];

        $layDeputyshipDto = new LayDeputyshipDto();
        $layDeputyshipDto->setDeputyUid('111111111')
            ->setCaseNumber('99999999');

        $this->mockLayDeputyAssembler->expects($this->once())
            ->method('assembleFromArray')
            ->with($row)
            ->willReturn($layDeputyshipDto);

        $this->mockEm->expects($this->once())->method('beginTransaction');
        $this->mockEm->expects($this->once())->method('getRepository')->willReturn($this->mockUserRepository);

        $this->mockUserRepository->expects($this->once())
            ->method('findPrimaryUserByDeputyUid')
            ->with('111111111')
            ->willThrowException(new NoResultException());

        // Test
        $output = $this->sut->processRow($row);

        // Assert
        $this->assertEquals([], $output['entityDetails']);
        $this->assertStringContainsString(
            'Error when creating entities for deputyUID 111111111 for case 99999999',
            $output['error']
        );
    }

    public function testProcessRowNoClientException()
    {
        // line 116
    }

    public function testProcessRowExistingClientNewReport()
    {
        // line 123
    }

    public function testProcessRowExistingClientCompatibleReport()
    {
        // line 123
    }

    public function testProcessRowExistingClientCompatibleHybridReport()
    {
        // line 144
    }

    public function testProcessRowNewClientNewReport()
    {
        // line 162
    }
}
