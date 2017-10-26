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
        $crLayHasPC = m::mock('\AppBundle\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('11111111')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('dpc123')
            ->shouldReceive('getDeputyNo')->withNoArgs()->andReturn('Dep1')
            ->getMock();

        $crLayNoPC = m::mock('\AppBundle\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('22222222')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputyNo')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        //Group MLD1 has a postcode for each of the three deputies
        $casrecMLD1A = m::mock('\AppBundle\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('mld1aa')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('unique')
            ->shouldReceive('getDeputyNo')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $casrecMLD1B = m::mock('\AppBundle\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('mld1bb')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('sibling')
            ->shouldReceive('getDeputyNo')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $casrecMLD1C = m::mock('\AppBundle\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('mld1bb')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('sibling')
            ->shouldReceive('getDeputyNo')->withNoArgs()->andReturn('MLDC')
            ->getMock();

        //Group MLD2 has a missing postcode for one of the two deputies
        $casrecMLD2A = m::mock('\AppBundle\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('mld2aa')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('sibling')
            ->shouldReceive('getDeputyNo')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $casrecMLD2B = m::mock('\AppBundle\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('sibling')
            ->shouldReceive('getDeputyNo')->withNoArgs()->andReturn('MLDB')
            ->getMock();


        $mockCasrecRepo = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findByCaseNumber')->with('11111111')->andReturn([$crLayHasPC])
            ->shouldReceive('findByCaseNumber')->with('22222222')->andReturn([$crLayNoPC])
            ->shouldReceive('findByCaseNumber')->with('33333333')->andReturn([$casrecMLD1A, $casrecMLD1B, $casrecMLD1C])
            ->shouldReceive('findByCaseNumber')->with('44444444')->andReturn([$casrecMLD2A, $casrecMLD2B])
            ->shouldReceive('findBy')->with([ 'caseNumber'=>'11111111', 'clientLastname' => 'csurn', 'deputySurname' => 'dsurn'])->andReturn([$crLayHasPC])
            ->shouldReceive('findBy')->with([ 'caseNumber'=>'wrong678', 'clientLastname' => 'csurn', 'deputySurname' => 'dsurn'])->andReturn([])
            ->shouldReceive('findBy')->with([ 'caseNumber'=>'11111111', 'clientLastname' => 'wrong', 'deputySurname' => 'dsurn'])->andReturn([])
            ->shouldReceive('findBy')->with([ 'caseNumber'=>'11111111', 'clientLastname' => 'csurn', 'deputySurname' => 'wrong'])->andReturn([])

            ->shouldReceive('findBy')->with([ 'caseNumber'=>'22222222', 'clientLastname' => 'csurn', 'deputySurname' => 'dsurn'])->andReturn([$crLayNoPC])
            ->shouldReceive('findBy')->with([ 'caseNumber'=>'33333333', 'clientLastname' => 'csurn', 'deputySurname' => 'mldunique'])->andReturn([$casrecMLD1A])
            ->shouldReceive('findBy')->with([ 'caseNumber'=>'33333333', 'clientLastname' => 'csurn', 'deputySurname' => 'sibling'])->andReturn([$casrecMLD1B, $casrecMLD1C])
            ->shouldReceive('findBy')->with([ 'caseNumber'=>'44444444', 'clientLastname' => 'csurn', 'deputySurname' => 'sibling'])->andReturn([$casrecMLD2A, $casrecMLD2B])
            ->getMock();

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\CasRec')->andReturn($mockCasrecRepo)
            ->getMock();

        $this->casrecVerificationService = new CasrecVerificationService($em);
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
        $this->assertFalse($this->casrecVerificationService->isMultiDeputyCase('11111111'));
        $this->assertTrue($this->casrecVerificationService->isMultiDeputyCase('33333333'));
        $this->assertTrue($this->casrecVerificationService->isMultiDeputyCase('44444444'));
    }

    /**
     * @test
     */
    public function validateNonMLDWithPostcode()
    {
        $this->assertTrue($this->casrecVerificationService->validate('11111111', 'CSurn', 'DSurn', 'DPC123'));

        // test each fail individually
        $failMessage = 'User registration: no matching record in casrec';
        try {
            $this->casrecVerificationService->validate('WRONG678', 'CSurn', 'DSurn', 'DPC123');
        } catch(\RuntimeException $e) {
            $this->assertEquals($failMessage, $e->getMessage());
        }

        try {
            $this->assertTrue($this->casrecVerificationService->validate('11111111', 'WRONG', 'DSurn', 'DPC123'));
        } catch(\RuntimeException $e) {
            $this->assertEquals($failMessage, $e->getMessage());
        }

        try {
            $this->assertTrue($this->casrecVerificationService->validate('11111111', 'CSurn', 'WRONG', 'DPC123'));
        } catch(\RuntimeException $e) {
            $this->assertEquals($failMessage, $e->getMessage());
        }

        try {
            $this->assertTrue($this->casrecVerificationService->validate('11111111', 'CSurn', 'DSurn', 'WRONG'));
        } catch(\RuntimeException $e) {
            $this->assertEquals($failMessage, $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function validateNonMLDWithNoPostcode()
    {
        $this->assertTrue($this->casrecVerificationService->validate('22222222', 'CSurn', 'DSurn', 'ANY ThinG'));
    }

    /**
     * @test
     */
    public function validateMLDUnique()
    {
        $this->assertTrue($this->casrecVerificationService->validate('33333333', 'CSurn', 'MLDUnique', 'MLD 1AA'));
    }

    /**
     * @test
     */
    public function validateSameAddressMLDSiblings()
    {
        $this->assertTrue($this->casrecVerificationService->validate('33333333', 'CSurn', 'Sibling', 'MLD 1BB'));
    }

    /**
     * @test
     */
    public function validateMLDSiblingsMissingPostcode()
    {
        // if all MLD postcodes are in casrec, the postcode check is run
        try {
            $this->assertTrue($this->casrecVerificationService->validate('11111111', 'CSurn', 'DSurn', 'DOEsnT MatteR'));
        } catch(\RuntimeException $e) {
            $this->assertEquals('User registration: no matching record in casrec', $e->getMessage());
        }

        // but if one MLD in casrec, the postcode check is skipped
        $this->assertTrue($this->casrecVerificationService->validate('44444444', 'CSurn', 'Sibling', 'DOEsnT MatteR'));
    }


    /**
     * @test
     */
    public function getLastMatchedDeputyNumbers()
    {
        $this->casrecVerificationService->validate('11111111', 'CSurn', 'DSurn', 'DPC123');
        $this->assertEquals(['Dep1'], $this->casrecVerificationService->getLastMatchedDeputyNumbers());
        $this->casrecVerificationService->validate('33333333', 'CSurn', 'Sibling', 'MLD 1BB');
        $this->assertEquals(['MLDB','MLDC'], $this->casrecVerificationService->getLastMatchedDeputyNumbers());
    }

}