<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\Uploader;

use Doctrine\ORM\EntityManager;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Repository\PreRegistrationRepository;
use OPG\Digideps\Backend\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDtoCollection;
use OPG\Digideps\Backend\v2\Registration\SelfRegistration\Factory\PreRegistrationCreationException;
use OPG\Digideps\Backend\v2\Registration\SelfRegistration\Factory\PreRegistrationFactory;
use OPG\Digideps\Backend\v2\Registration\Uploader\LayDeputyshipProcessor;
use OPG\Digideps\Backend\v2\Registration\Uploader\LayDeputyshipUploader;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LayDeputyshipUploaderTest extends KernelTestCase
{
    private EntityManager|MockObject $em;
    private PreRegistrationFactory|MockObject $factory;
    private SiriusToLayDeputyshipDtoAssembler|MockObject $layDeputyAssembler;
    private LayDeputyshipProcessor|MockObject $layDeputyProcessor;
    private LoggerInterface $logger;

    private LayDeputyshipUploader $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->factory = $this->createMock(PreRegistrationFactory::class);
        $this->layDeputyAssembler = $this->createMock(SiriusToLayDeputyshipDtoAssembler::class);
        $this->layDeputyProcessor = $this->createMock(LayDeputyshipProcessor::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sut = new LayDeputyshipUploader(
            $this->em,
            $this->factory,
            $this->layDeputyAssembler,
            $this->layDeputyProcessor,
            $this->logger,
        );
    }

    #[Test]
    public function throwsExceptionIfDataSetTooLarge(): void
    {
        $this->expectException(\RuntimeException::class);
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < LayDeputyshipUploader::MAX_UPLOAD + 1; ++$i) {
            $collection->append(new LayDeputyshipDto());
        }

        $this->sut->upload($collection);
    }

    #[Test]
    public function persistsAnEntryForEachValidDeputyship(): void
    {
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < 3; ++$i) {
            $collection->append($this->buildLayDeputyshipDto($i));
        }

        // Assert 3 PreRegistration entities will be created.
        $this->factory
            ->expects($this->exactly(3))
            ->method('createFromDto')
            ->willReturnOnConsecutiveCalls(new PreRegistration([]), new PreRegistration([]), new PreRegistration([]));

        $return = $this->sut->upload($collection);

        $this->assertEquals(3, $return['added']);
        $this->assertCount(0, $return['errors']);
    }

    #[Test]
    public function ignoresDeputyshipsWithInvalidDeputyshipData(): void
    {
        $collection = new LayDeputyshipDtoCollection();
        $collection->append($this->buildLayDeputyshipDto(1));

        // Ensure factory will throw an exception
        $this
            ->factory
            ->method('createFromDto')
            ->willThrowException(new PreRegistrationCreationException('Unable to create PreRegistration entity'));

        $return = $this->sut->upload($collection);

        $this->assertEquals(0, $return['added']);
        $this->assertCount(1, $return['errors']);
        $this->assertEquals('ERROR IN LINE: Unable to create PreRegistration entity', $return['errors'][0]);
    }

    private function buildLayDeputyshipDto(int $count): LayDeputyshipDto
    {
        return new LayDeputyshipDto()
            ->setCaseNumber('case-' . $count)
            ->setDeputyUid('depnum-' . $count);
    }

    #[Test]
    public function testHandleNewMultiClientsNoNewClientsToAdd(): void
    {
        $mockPreRegistrationRepo = $this->createMock(PreRegistrationRepository::class);

        $mockPreRegistrationRepo->expects($this->once())
            ->method('getNewClientsForExistingDeputiesArray')
            ->willReturn([]);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('No new multi clients to add');

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(PreRegistration::class)
            ->willReturn($mockPreRegistrationRepo);

        $actual = $this->sut->handleNewMultiClients();

        $this->assertEquals(0, $actual['new-clients-found']);
        $this->assertEquals(0, $actual['clients-added']);
        $this->assertEquals([], $actual['errors']);
    }

    #[Test]
    public function testHandleNewMultiClients(): void
    {
        $case1 = ['Case' => '11111111'];
        $case2 = ['Case' => '22222222'];
        $case3 = ['Case' => '33333333'];

        $mockPreRegistrationRepo = $this->createMock(PreRegistrationRepository::class);
        $mockPreRegistrationRepo->expects($this->once())
            ->method('getNewClientsForExistingDeputiesArray')
            ->willReturn([$case1, $case2, $case3]);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(PreRegistration::class)
            ->willReturn($mockPreRegistrationRepo);

        $this->layDeputyAssembler->expects($this->exactly(3))
            ->method('assembleFromArray')
            ->willReturnCallback(function (array $case): LayDeputyshipDto {
                return new LayDeputyshipDto()->setCaseNumber($case['Case']);
            });

        $this->layDeputyProcessor->expects($this->exactly(3))
            ->method('processLayDeputyship')
            ->willReturnCallback(function (LayDeputyshipDto $dto) use ($case1, $case2, $case3): ?array {
                if ($dto->getCaseNumber() === $case1['Case']) {
                    return [
                        'entityDetails' => ['clientCaseNumber' => '11111111'],
                        'message' => '',
                        'error' => null,
                    ];
                }
                if ($dto->getCaseNumber() === $case2['Case']) {
                    return [
                        'entityDetails' => ['clientCaseNumber' => '22222222'],
                        'message' => '',
                        'error' => null,
                    ];
                }
                if ($dto->getCaseNumber() === $case3['Case']) {
                    return [
                        'entityDetails' => [],
                        'message' => '',
                        'error' => 'an error occurred',
                    ];
                }

                return null;
            });

        $actual = $this->sut->handleNewMultiClients();

        $this->assertEquals(3, $actual['new-clients-found']);
        $this->assertEquals(2, $actual['clients-added']);
        $this->assertEquals(['an error occurred'], $actual['errors']);
    }
}
