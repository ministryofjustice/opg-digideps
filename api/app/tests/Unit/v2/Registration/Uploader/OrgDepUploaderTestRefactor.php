<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Factory\OrganisationFactory;
use App\Tests\Unit\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;
use App\v2\Assembler\ClientAssembler;
use App\v2\Assembler\DeputyAssembler;
use App\v2\Registration\Assembler\CourtOrderDtoAssembler;
use App\v2\Registration\DTO\CourtOrderDto;
use App\v2\Registration\SelfRegistration\Factory\CourtOrderFactory;
use App\v2\Registration\Uploader\OrgDeputyshipUploader;
use DateTime;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrgDeputyshipUploaderTest extends KernelTestCase
{
    private readonly OrgDeputyshipUploader $sut;
    private readonly CourtOrderDtoAssembler $courtOrderAssembler;
    private readonly CourtOrderFactory $courtOrderFactory;
    private readonly OrganisationFactory $orgFactory;
    private readonly ClientAssembler $clientAssembler;
    private readonly DeputyAssembler $deputyAssembler;

    public function setUp(): void
    {
        $this->emMock = \Mockery::mock(EntityManager::class);
        $this->orgFactory = \Mockery::mock(OrganisationFactory::class);
        $this->clientAssembler = \Mockery::mock(ClientAssembler::class);
        $this->deputyAssembler = \Mockery::mock(DeputyAssembler::class);
        $this->logger = \Mockery::mock(LoggerInterface::class);
        $this->courtOrderAssembler = \Mockery::mock(CourtOrderDtoAssembler::class);
        $this->courtOrderFactory = \Mockery::mock(CourtOrderFactory::class);

        $this->sut = new OrgDeputyshipUploader(
            $this->emMock,
            $this->orgFactory,
            $this->clientAssembler,
            $this->deputyAssembler,
            $this->logger,
            $this->courtOrderAssembler,
            $this->courtOrderFactory,
        );
    }

    /** @test */
    public function uploadNewDeputiesAreCreated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->emMock
            ->shouldReceive('clear')
            ->once();

        $this->emMock
            ->shouldReceive('getRepository->findByCaseNumber')
            ->with($deputyships[0]->getCaseNumber())
            ->andReturn(null);

        $this->emMock
            ->shouldReceive('getRepository->findCourtOrderByUid')
            ->with($deputyships[0]->getCourtOrderUid())
            ->andReturn(null);

        $courtOrderDTO = new CourtOrderDto();
        $this->courtOrderAssembler
            ->shouldReceive('assembleFromDto')
            ->with($deputyships[0])
            ->andReturn($courtOrderDTO);

        $courtOrder = new CourtOrder([
            'CourtOrderUid' => $deputyships[0]->getCourtOrderUid(),
            'Type' => $deputyships[0]->getHybrid(),
            'Active' => true,
        ]);
        $this->courtOrderFactory
            ->shouldReceive('createFromDto')
            ->with($courtOrderDTO)
            ->andReturn($courtOrder);

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->with($courtOrder)
            ->shouldReceive('flush')
            ->once();

        $this->emMock
            ->shouldReceive('getRepository->findOneBy')
            ->with(['deputyUid' => $deputyships[0]->getDeputyUid()])
            ->andReturn(null);

        $deputy = new Deputy();
        $deputy->setId($deputyships[0]->getDeputyUid());

        $this->deputyAssembler
            ->shouldReceive('assembleFromOrgDeputyshipDto')
            ->with($deputyships[0])
            ->andReturn($deputy);

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->with($deputy)
            ->shouldReceive('flush')
            ->once();

        $this->emMock
            ->shouldReceive('getRepository->findByEmailIdentifier')
            ->with($deputyships[0]->getDeputyEmail())
            ->andReturn(null);

        $org = new Organisation();
        $orgId = 200000001;
        $org->setId($orgId);

        $this->orgFactory
            ->shouldReceive('createFromFullEmail')
            ->with($deputyships[0]->getOrganisationName(), $deputyships[0]->getDeputyEmail())
            ->andReturn($org);

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->with($org)
            ->shouldReceive('flush')
            ->once();

        $client = new Client();
        $client->setCaseNumber($deputyships[0]->getCaseNumber());

        $this->clientAssembler
            ->shouldReceive('assembleFromOrgDeputyshipDto')
            ->with($deputyships[0])
            ->andReturn($client);

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->with($client)
            ->shouldReceive('flush')
            ->once();

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->withArgs(function ($args) {
                // closure required due to Mock expecting exact memory match to object created in uploader
                // need to refactor handleReport method, so report object is created by a factory pattern class.
                return ($args instanceof Report) ? true : false;
            })
            ->shouldReceive('flush')
            ->once();

        $result = $this->sut->upload($deputyships);
        $reportId = $client->getCaseNumber() . '-' . $deputyships[0]->getReportEndDate()->format('Y-m-d');

        self::assertEquals($deputyships[0]->getCaseNumber(), $result['added']['clients'][0]);
        self::assertEquals($deputyships[0]->getDeputyUid(), $result['added']['deputies'][0]);
        self::assertEquals($reportId, $result['added']['reports'][0]);
        self::assertEquals($orgId, $result['added']['organisations'][0]);
        self::assertEquals($deputyships[0]->getCourtOrderUid(), $result['added']['court_orders'][0]);
        self::assertTrue(empty($result['errors']['messages']));
    }

    /** @test */
    public function uploadExistingDeputiesAreNotProcessed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->emMock
            ->shouldReceive('clear')
            ->once();

        $this->emMock
            ->shouldReceive('getRepository->findByCaseNumber')
            ->with($deputyships[0]->getCaseNumber())
            ->andReturn(null);

        $this->emMock
            ->shouldReceive('getRepository->findCourtOrderByUid')
            ->with($deputyships[0]->getCourtOrderUid())
            ->andReturn(null);

        $courtOrderDTO = new CourtOrderDto();
        $this->courtOrderAssembler
            ->shouldReceive('assembleFromDto')
            ->with($deputyships[0])
            ->andReturn($courtOrderDTO);

        $courtOrder = new CourtOrder([
            'CourtOrderUid' => $deputyships[0]->getCourtOrderUid(),
            'Type' => $deputyships[0]->getHybrid(),
            'Active' => true,
        ]);
        $this->courtOrderFactory
            ->shouldReceive('createFromDto')
            ->with($courtOrderDTO)
            ->andReturn($courtOrder);

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->with($courtOrder)
            ->shouldReceive('flush')
            ->once();

        $deputy = new Deputy();
        $deputy
            ->setId($deputyships[0]->getDeputyUid())
            ->setAddress1($deputyships[0]->getDeputyAddress1())
            ->setAddress2($deputyships[0]->getDeputyAddress2())
            ->setAddress3($deputyships[0]->getDeputyAddress3())
            ->setAddress4($deputyships[0]->getDeputyAddress4())
            ->setAddress5($deputyships[0]->getDeputyAddress5())
            ->setAddressPostcode($deputyships[0]->getDeputyPostcode())
            ->setFirstname($deputyships[0]->getOrganisationName())
            ->setLastname('')
            ->setEmail1($deputyships[0]->getDeputyEmail());

        $this->emMock
            ->shouldReceive('getRepository->findOneBy')
            ->with(['deputyUid' => $deputyships[0]->getDeputyUid()])
            ->andReturn($deputy);

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->with($deputy)
            ->shouldReceive('flush')
            ->once();

        $this->emMock
            ->shouldReceive('getRepository->findByEmailIdentifier')
            ->with($deputyships[0]->getDeputyEmail())
            ->andReturn(null);

        $org = new Organisation();
        $orgId = 200000001;
        $org->setId($orgId);

        $this->orgFactory
            ->shouldReceive('createFromFullEmail')
            ->with($deputyships[0]->getOrganisationName(), $deputyships[0]->getDeputyEmail())
            ->andReturn($org);

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->with($org)
            ->shouldReceive('flush')
            ->once();

        $client = new Client();
        $client->setCaseNumber($deputyships[0]->getCaseNumber());

        $this->clientAssembler
            ->shouldReceive('assembleFromOrgDeputyshipDto')
            ->with($deputyships[0])
            ->andReturn($client);

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->with($client)
            ->shouldReceive('flush')
            ->once();

        $this->emMock
            ->shouldReceive('persist')
            ->once()
            ->withArgs(function ($args) {
                // closure required due to Mock expecting exact memory match to object created in uploader
                // need to refactor handleReport method, so report object is created by a factory pattern class.
                return ($args instanceof Report) ? true : false;
            })
            ->shouldReceive('flush')
            ->once();

        $result = $this->sut->upload($deputyships);

        self::assertEmpty($result['added']['deputies']);
        self::assertEmpty($result['updated']['deputies']);
        self::assertEmpty($result['errors']['messages']);
    }
}
