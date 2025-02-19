<?php

namespace App\Tests\Integration\Search;

use App\Service\Search\ClientSearchFilter;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientSearchFilterTest extends TestCase
{
    /** @var EntityManagerInterface|MockObject */
    private $em;

    /** @var QueryBuilder */
    private $qb;

    /** @var ClientSearchFilter */
    private $sut;

    /**
     * @test
     */
    public function handleSearchTermFilterAddsCaseNumberFilterIfGivenCaseNumber()
    {
        $this
            ->initEntityManager()
            ->initQueryBuilder()
            ->invokeTestWithSearchTerm('12345678')
            ->assertQueryEquals('SELECT c WHERE lower(c.caseNumber) = :cn')
            ->assertQueryParametersEqual([['name' => 'cn', 'value' => '12345678']]);
    }

    /**
     * @test
     */
    public function handleSearchTermFilterAddsBroadNameFilterIfGivenSingleSearchTerm()
    {
        $this
            ->initEntityManager()
            ->initQueryBuilder()
            ->invokeTestWithSearchTerm('John')
            ->assertQueryEquals('SELECT c WHERE lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike')
            ->assertQueryParametersEqual([['name' => 'qLike', 'value' => '%john%']]);
    }

    /**
     * @test
     */
    public function handleSearchTermFilterAddsExactNameFilterIfGivenDoubleSearchTerm()
    {
        $this
            ->initEntityManager()
            ->initQueryBuilder()
            ->invokeTestWithSearchTerm('John Adams')
            ->assertQueryEquals('SELECT c WHERE (lower(c.firstname) = :firstname AND lower(c.lastname) = :lastname)')
            ->assertQueryParametersEqual([
                ['name' => 'firstname', 'value' => 'john'],
                ['name' => 'lastname', 'value' => 'adams'],
            ]);
    }

    private function initEntityManager(): ClientSearchFilterTest
    {
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->em->method('getConfiguration')->willReturn(new Configuration());
        $this->em->method('createQuery')->willReturn(new Query($this->em));

        return $this;
    }

    private function initQueryBuilder(): ClientSearchFilterTest
    {
        $this->qb = new QueryBuilder($this->em);
        $this->qb->select('c');

        return $this;
    }

    private function invokeTestWithSearchTerm(string $searchTerm): ClientSearchFilterTest
    {
        $this->sut = new ClientSearchFilter();
        $this->sut->handleSearchTermFilter($searchTerm, $this->qb, 'c');

        return $this;
    }

    private function assertQueryEquals(string $expected): ClientSearchFilterTest
    {
        $this->assertEquals($expected, $this->qb->getDQL());

        return $this;
    }

    private function assertQueryParametersEqual(array $expectedParams): void
    {
        $expectedParamsCount = count($expectedParams);
        for ($i = 0; $i < $expectedParamsCount; ++$i) {
            $this->assertEquals($expectedParams[$i]['name'], $this->qb->getParameters()->offsetGet($i)->getName());
            $this->assertEquals($expectedParams[$i]['value'], $this->qb->getParameters()->offsetGet($i)->getValue());
        }
    }
}
