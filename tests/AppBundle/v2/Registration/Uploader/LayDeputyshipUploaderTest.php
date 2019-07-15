<?php

namespace Tests\AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Entity\User;
use AppBundle\Service\ReportService;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecCreationException;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use AppBundle\v2\Registration\Uploader\LayDeputyshipUploader;
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
    protected function setUp()
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
     * @expectedException \RuntimeException
     */
    public function throwsExceptionIfDataSetTooLarge()
    {
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < LayDeputyshipUploader::MAX_UPLOAD + 1; $i++) {
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

        for ($i = 0; $i < 3; $i++) {
            $collection->append($this->buildLayDeputyshipDto($i));
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
    }

    /**
     * @test
     */
    public function updatesReportTypeOfActiveReportsIfRequired()
    {
        $collection = new LayDeputyshipDtoCollection();
        $collection->append($this->buildLayDeputyshipDto(1));
        
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
        $this->assertEquals('103', $activeReport->getType());
    }

    /**
     * @test
     */
    public function ignoresDeputyshipsWithInvalidDeputyshipData()
    {
        $collection = new LayDeputyshipDtoCollection();
        $collection->append($this->buildLayDeputyshipDto(1));

        // Ensure factory will throw an exception
        $this
            ->factory
            ->method('createFromDto')
            ->willThrowException(new CasRecCreationException('Unable to create CasRec entity'));

        $this->assertReportTypesWillNotBeUpdated();

        $return = $this->sut->upload($collection);

        $this->assertEquals(0, $return['added']);
        $this->assertCount(1, $return['errors']);
        $this->assertEquals('ERROR IN LINE 2: Unable to create CasRec entity', $return['errors'][0]);
    }

    /**
     * @param $count
     * @return LayDeputyshipDto
     */
    private function buildLayDeputyshipDto($count): LayDeputyshipDto
    {
        return (new LayDeputyshipDto())
            ->setCaseNumber('case-'.$count)
            ->setDeputyNumber('depnum-'.$count);
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
