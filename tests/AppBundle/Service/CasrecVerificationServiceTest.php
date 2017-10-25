<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\CasrecVerificationService;
use Doctrine\ORM\ORMInvalidArgumentException;
use Mockery as m;

class CasrecVerificationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CasrecVerificationService
     */
    private $casrecVerificationService;

    public function setup()
    {
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function isMultiDeputyCase()
    {
        $mockCasrec = m::mock('alias:\AppBundle\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('normaliseCaseNumber')->with('12345')->andReturn('12345')
            ->shouldReceive('normaliseCaseNumber')->with('56789')->andReturn('56789')
            ->getMock();

        $mockCasrecRepo = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findByCaseNumber')->with('12345')->andReturn([$mockCasrec])
            ->shouldReceive('findByCaseNumber')->with('56789')->andReturn([$mockCasrec, $mockCasrec, $mockCasrec])
            ->getMock();

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\CasRec')->andReturn($mockCasrecRepo)
            ->getMock();

        $this->casrecVerificationService = new CasrecVerificationService($em);
        $this->assertFalse($this->casrecVerificationService->isMultiDeputyCase('12345'));
        $this->assertTrue($this->casrecVerificationService->isMultiDeputyCase('56789'));
    }

    /**
     * @test
     */
    public function validate()
    {
        $mockCasrecRepo = m::mock('\Doctrine\ORM\Entity\Repository\CasRecRepository')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findBy')->with(['11111111', 'CSurn', 'DSurn'])
            ->getMock();

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\CasRec')->andReturn($mockCasrecRepo)
            ->getMock();

        $this->casrecVerificationService = new CasrecVerificationService($em);
        // test with all 4 correct
        $this->casrecVerificationService->validate('11111111', 'CSurn', 'DSurn', 'DPC123');
        // test each fail individually
        // postcode present
        //    test with correct postcode
        //    test with incorrect postcode

    }


//    /**
//     * @test
//     */
//    public function getLastMatchedDeputyNumbers()
//    {
//    }
//


    /**
     * @test
     * @expectedException \Doctrine\ORM\ORMInvalidArgumentException
     */

}