<?php

namespace Tests\AppBundle\v2\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\v2\Assembler\DeputyAssembler;
use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\Repository\ClientRepository;
use AppBundle\v2\Repository\DeputyRepository;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyRepositoryTest extends KernelTestCase
{
    /** @var DeputyRepository */
    private $repository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $clientRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $deputyAssembler;

    /** @var EntityManager */
    private $em;

    /** @var Fixtures */
    private $fixtures;

    /** @var array */
    private $result = [];

    /** @var DeputyDto */
    private $expectedDto;

    public static function setUpBeforeClass()
    {
        Fixtures::deleteReportsData();
    }

    public function setUp()
    {
        $kernel = static::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $this->fixtures = new Fixtures($this->em);

        $this->clientRepository = $this->getMockBuilder(ClientRepository::class)->disableOriginalConstructor()->getMock();
        $this->deputyAssembler = $this->getMockBuilder(DeputyAssembler::class)->disableOriginalConstructor()->getMock();

        $this->repository = new DeputyRepository(
            $this->em,
            $this->em->getRepository(User::class),
            $this->clientRepository,
            $this->deputyAssembler
        );
    }

    /**
     * @test
     */
    public function getDtoByIdReturnsDeputyDtoForGivenDeputy()
    {
        $deputy = $this->fixtures->createUser([
            'setFirstname' => 'Deputy',
            'setLastname' => 'User',
            'setEmail' => 'deputy@test.com',
            'setRoleName' => 'ADMIN',
            'setAddressPostcode' => 'NG54RF',
            'setNdrEnabled' => true
        ]);

        $this
            ->flushDatabase()
            ->ensureClientsWillBeAttachedToDeputy($deputy)
            ->ensureDeputyAssemblerWillBuildDto($deputy)
            ->invokeRepositoryMethod('getDtoById', $deputy->getId())
            ->assertDeputyDtoIsReturned();
    }

    /**
     * @return $this
     */
    private function flushDatabase()
    {
        $this->fixtures->flush();

        return $this;
    }

    /**
     * @param User $deputy
     * @return $this
     */
    private function ensureClientsWillBeAttachedToDeputy(User $deputy)
    {
        $this
            ->clientRepository
            ->expects($this->once())
            ->method('getDtoDataArrayByDeputy')
            ->with($deputy->getId())
            ->willReturn([['Alpha' => 'Client']]);

        return $this;
    }

    /**
     * @param User $deputy
     * @return $this
     */
    private function ensureDeputyAssemblerWillBuildDto(User $deputy)
    {
        $this->expectedDto = new DeputyDto(
            $deputy->getId(),
            'Deputy',
            'User',
            'deputy@test.com',
            'ADMIN',
            'NG54RF',
            true
        );

        $this
            ->deputyAssembler
            ->expects($this->once())
            ->method('assembleFromArray')
            ->with([
                'id' => $deputy->getId(),
                'firstname' => 'Deputy',
                'lastname' => 'User',
                'email' => 'deputy@test.com',
                'role_name' => 'ADMIN',
                'address_postcode' => 'NG54RF',
                'odr_enabled' => true,
                'clients' => [['Alpha' => 'Client']]
            ])
            ->willReturn($this->expectedDto);

        return $this;
    }

    /**
     * @param $method
     * @param $id
     * @return $this
     */
    private function invokeRepositoryMethod($method, $id)
    {
        $this->result = $this->repository->{$method}($id);

        return $this;
    }

    /**
     *
     */
    private function assertDeputyDtoIsReturned()
    {
        $this->assertSame($this->result, $this->expectedDto);
    }
}
