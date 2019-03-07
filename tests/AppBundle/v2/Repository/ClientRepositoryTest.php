<?php

namespace Tests\AppBundle\v2\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\v2\Repository\ClientRepository;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClientRepositoryTest extends KernelTestCase
{
    /** @var ClientRepository */
    private $repository;

    /** @var EntityManager */
    private $em;

    /** @var Fixtures */
    private $fixtures;

    /** @var array */
    private $result = [];

    public static function setUpBeforeClass()
    {
        Fixtures::deleteReportsData();
    }

    public function setUp()
    {
        $kernel = static::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $this->fixtures = new Fixtures($this->em);
        $this->repository = new ClientRepository($this->em, $this->em->getRepository(Client::class));
    }

    /**
     * @test
     */
    public function getDtoDataArrayByDeputyReturnsClientDataForEachClientBelongingToDeputy()
    {
        $deputy = $this->fixtures->createUser();

        $this
            ->ensureDeputyHasClientWithTwoReportsAndOneNdr($deputy)
            ->ensureDeputyHasClientWithNoReportsAndOneNdr($deputy)
            ->ensureDeputyHasClientWithOneReportAndNoNdr($deputy)
            ->flushDatabase()
            ->invokeRepositoryMethod('getDtoDataArrayByDeputy', $deputy->getId())
            ->assertClientDataForEachClientIsReturned();
    }

    /**
     * @param User $deputy
     * @return ClientRepositoryTest
     */
    private function ensureDeputyHasClientWithTwoReportsAndOneNdr(User $deputy)
    {
        $client = $this->createClientForDeputy($deputy, ['Alpha', 'Client', 'alpha@test.com', 'alphacasenum']);

        return $this
            ->createReportFor($client)
            ->createReportFor($client)
            ->createNdrFor($client);
    }

    /**
     * @param User $deputy
     * @return ClientRepositoryTest
     */
    private function ensureDeputyHasClientWithNoReportsAndOneNdr(User $deputy)
    {
        $client = $this->createClientForDeputy($deputy, ['Beta', 'Client', 'beta@test.com', 'betacasenum']);

        return $this->createNdrFor($client);
    }

    /**
     * @param User $deputy
     * @return ClientRepositoryTest
     */
    private function ensureDeputyHasClientWithOneReportAndNoNdr(User $deputy)
    {
        $client = $this->createClientForDeputy($deputy, ['Charlie', 'Client', 'charlie@test.com', 'charliecasenum']);

        return $this->createReportFor($client);
    }

    /**
     * @param User $deputy
     * @param array $details
     * @return Client
     */
    private function createClientForDeputy(User $deputy, array $details)
    {
        return $this->fixtures->createClient($deputy, [
            'setFirstname' => $details[0],
            'setLastname' => $details[1],
            'setEmail' => $details[2],
            'setCaseNumber' => $details[3]
        ]);
    }

    /**
     * @param Client $client
     * @return $this
     */
    private function createReportFor(Client $client)
    {
        $this->fixtures->createReport($client);

        return $this;
    }

    /**
     * @param Client $client
     * @return $this
     */
    private function createNdrFor(Client $client)
    {
        $this->fixtures->createNdr($client);

        return $this;
    }

    private function flushDatabase()
    {
        $this->fixtures->flush();

        return $this;
    }

    /**
     * @param $method
     * @param $id
     * @return ClientRepositoryTest
     */
    private function invokeRepositoryMethod($method, $id)
    {
        $this->result = $this->repository->{$method}($id);

        return $this;
    }

    private function assertClientDataForEachClientIsReturned()
    {
        $this->assertCount(3, $this->result);

        $this->assertEquals('Alpha', $this->result[0]['firstname']);
        $this->assertEquals('Client', $this->result[0]['lastname']);
        $this->assertEquals('alpha@test.com', $this->result[0]['email']);
        $this->assertEquals('alphacasenum', $this->result[0]['case_number']);
        $this->assertEquals(2, $this->result[0]['report_count']);
        $this->assertEquals(1, $this->result[0]['ndr_id']);

        $this->assertEquals('Beta', $this->result[1]['firstname']);
        $this->assertEquals('Client', $this->result[1]['lastname']);
        $this->assertEquals('beta@test.com', $this->result[1]['email']);
        $this->assertEquals('betacasenum', $this->result[1]['case_number']);
        $this->assertEquals(0, $this->result[1]['report_count']);
        $this->assertEquals(2, $this->result[1]['ndr_id']);

        $this->assertEquals('Charlie', $this->result[2]['firstname']);
        $this->assertEquals('Client', $this->result[2]['lastname']);
        $this->assertEquals('charlie@test.com', $this->result[2]['email']);
        $this->assertEquals('charliecasenum', $this->result[2]['case_number']);
        $this->assertEquals(1, $this->result[2]['report_count']);
        $this->assertNull($this->result[2]['ndr_id']);
    }
}
