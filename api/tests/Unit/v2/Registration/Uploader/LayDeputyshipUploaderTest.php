<?php

namespace App\Tests\Unit\v2\Registration\Uploader;

use App\Entity\CasRec;
use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\DTO\LayDeputyshipDtoCollection;
use App\v2\Registration\SelfRegistration\Factory\CasRecCreationException;
use App\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use App\v2\Registration\Uploader\LayDeputyshipUploader;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class LayDeputyshipUploaderTest extends TestCase
{
    /** @var EntityManager | \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var ReportRepository | \PHPUnit_Framework_MockObject_MockObject */
    protected $reportRepository;

    /** @var CasRecFactory | \PHPUnit_Framework_MockObject_MockObject */
    private $factory;

    /** @var LayDeputyshipUploader */
    private $sut;

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        $this->em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->reportRepository = $this->getMockBuilder(ReportRepository::class)->disableOriginalConstructor()->getMock();
        $this->factory = $this->getMockBuilder(CasRecFactory::class)->disableOriginalConstructor()->enableArgumentCloning()->getMock();

        $this->sut = new LayDeputyshipUploader(
            $this->em,
            $this->reportRepository,
            $this->factory
        );
    }

    /**
     * @test
     */
    public function throwsExceptionIfDataSetTooLarge()
    {
        $this->expectException(\RuntimeException::class);
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < LayDeputyshipUploader::MAX_UPLOAD + 1; ++$i) {
            $collection->append(new LayDeputyshipDto());
        }

        $this->sut->upload($collection);
    }

    /**
     * @test
     */
    public function persistsAnEntryForEachValidDeputyship()
    {
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < 3; ++$i) {
            $collection->append($this->buildLayDeputyshipDto($i, CasRec::CASREC_SOURCE));
        }

        // Assert 3 CasRec entities will be created.
        $this->factory
            ->expects($this->exactly(3))
            ->method('createFromDto')
            ->willReturnOnConsecutiveCalls(new CasRec([]), new CasRec([]), new CasRec([]));

        // Assert Report Types will not be updated (not relevant for this test).
        $this->reportRepository
            ->expects($this->once())
            ->method('findAllActiveReportsByCaseNumbersAndRole')
            ->with(['case-0', 'case-1', 'case-2'], User::ROLE_LAY_DEPUTY)
            ->willReturn([]);

        $return = $this->sut->upload($collection);

        $this->assertEquals(3, $return['added']);
        $this->assertCount(0, $return['errors']);
        $this->assertEquals(CasRec::CASREC_SOURCE, $return['source']);
    }

    /**
     * @test
     */
    public function updatesReportTypeOfActiveReportsIfRequired()
    {
        $collection = new LayDeputyshipDtoCollection();
        $collection->append($this->buildLayDeputyshipDto(1, CasRec::SIRIUS_SOURCE));

        $casRec = new CasRec(['Typeofrep' => 'opg103', 'Corref' => 'l3']);

        $this->factory
            ->expects($this->once())
            ->method('createFromDto')
            ->willReturnOnConsecutiveCalls($casRec);

        // Ensure an existing Client is found with an active Report whose type is different to the new type in the upload.
        $existingClient = (new Client())->setCaseNumber('case-1');
        $activeReport = new Report($existingClient, '102', new \DateTime(), new \DateTime(), false);
        $this->reportRepository
            ->expects($this->once())
            ->method('findAllActiveReportsByCaseNumbersAndRole')
            ->with(['case-1'], User::ROLE_LAY_DEPUTY)
            ->willReturn([$activeReport]);

        $return = $this->sut->upload($collection);
        $this->assertEquals(1, $return['added']);
        $this->assertCount(0, $return['errors']);
        $this->assertEquals(CasRec::SIRIUS_SOURCE, $return['source']);
        $this->assertEquals('103', $activeReport->getType());
    }

    /**
     * @test
     */
    public function ignoresDeputyshipsWithInvalidDeputyshipData()
    {
        $collection = new LayDeputyshipDtoCollection();
        $collection->append($this->buildLayDeputyshipDto(1, CasRec::SIRIUS_SOURCE));

        // Ensure factory will throw an exception
        $this
            ->factory
            ->method('createFromDto')
            ->willThrowException(new CasRecCreationException('Unable to create CasRec entity'));

        $this->assertReportTypesWillNotBeUpdated();

        $return = $this->sut->upload($collection);

        $this->assertEquals(0, $return['added']);
        $this->assertCount(1, $return['errors']);
        $this->assertEquals(CasRec::SIRIUS_SOURCE, $return['source']);
        $this->assertEquals('ERROR IN LINE 2: Unable to create CasRec entity', $return['errors'][0]);
    }

    /**
     * @param $count
     */
    private function buildLayDeputyshipDto($count, $source): LayDeputyshipDto
    {
        return (new LayDeputyshipDto())
            ->setCaseNumber('case-'.$count)
            ->setDeputyNumber('depnum-'.$count)
            ->setSource($source);
    }

    private function assertReportTypesWillNotBeUpdated(): void
    {
        $this->reportRepository
            ->expects($this->once())
            ->method('findAllActiveReportsByCaseNumbersAndRole')
            ->with([], User::ROLE_LAY_DEPUTY)
            ->willReturn([]);
    }
}
