<?php

namespace App\Tests\Unit\Service;

use App\Service\CasrecVerificationService;
use Doctrine\ORM\EntityManager;
use Mockery as m;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class CasrecVerificationServiceTest extends WebTestCase
{
    use ProphecyTrait;

    /**
     * @var CasrecVerificationService
     */
    private $casrecVerificationService;

    public static function setUpBeforeClass(): void
    {
        static::createClient(['environment' => 'test', 'debug' => false]);
    }

    public function setUp(): void
    {
        $crLayHasPC = m::mock('\App\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('11111111')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('DPC123')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('Dep1')
            ->getMock();

        $crLayNoPC = m::mock('\App\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('22222222')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        //Group MLD1 has a postcode for each of the three deputies
        $casrecMLD1A = m::mock('\App\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1AA')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('unique')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $casrecMLD1B = m::mock('\App\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1BB')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $casrecMLD1C = m::mock('\App\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1BB')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDC')
            ->getMock();

        //Group MLD2 has a missing postcode for one of the two deputies
        $casrecMLD2A = m::mock('\App\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD2AA')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $casrecMLD2B = m::mock('\App\Entity\Casrec')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $mockCasrecRepo = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findByCaseNumber')->with('11111111')->andReturn([$crLayHasPC])
            ->shouldReceive('findByCaseNumber')->with('22222222')->andReturn([$crLayNoPC])
            ->shouldReceive('findByCaseNumber')->with('33333333')->andReturn([$casrecMLD1A, $casrecMLD1B, $casrecMLD1C])
            ->shouldReceive('findByCaseNumber')->with('44444444')->andReturn([$casrecMLD2A, $casrecMLD2B])
            ->shouldReceive('findBy')->with(['caseNumber' => '11111111', 'clientLastname' => 'CSurn', 'deputySurname' => 'DSurn'])->andReturn([$crLayHasPC])
            ->shouldReceive('findBy')->with(['caseNumber' => 'WRONG678', 'clientLastname' => 'CSurn', 'deputySurname' => 'DSurn'])->andReturn([])
            ->shouldReceive('findBy')->with(['caseNumber' => '11111111', 'clientLastname' => 'WRONG', 'deputySurname' => 'DSurn'])->andReturn([])
            ->shouldReceive('findBy')->with(['caseNumber' => '11111111', 'clientLastname' => 'CSurn', 'deputySurname' => 'WRONG'])->andReturn([])
            ->shouldReceive('findBy')->with(['caseNumber' => '22222222', 'clientLastname' => 'CSurn', 'deputySurname' => 'DSurn'])->andReturn([$crLayNoPC])
            ->shouldReceive('findBy')->with(['caseNumber' => '33333333', 'clientLastname' => 'CSurn', 'deputySurname' => 'MLDUnique'])->andReturn([$casrecMLD1A])
            ->shouldReceive('findBy')->with(['caseNumber' => '33333333', 'clientLastname' => 'CSurn', 'deputySurname' => 'Sibling'])->andReturn([$casrecMLD1B, $casrecMLD1C])
            ->shouldReceive('findBy')->with(['caseNumber' => '44444444', 'clientLastname' => 'CSurn', 'deputySurname' => 'Sibling'])->andReturn([$casrecMLD2A, $casrecMLD2B])
            ->getMock();

        $em = m::mock(EntityManager::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('App\Entity\CasRec')->andReturn($mockCasrecRepo)
            ->getMock();

        $serializer = self::prophesize(SerializerInterface::class);

        $this->casrecVerificationService = new CasrecVerificationService($em, $serializer->reveal());
    }

    public function tearDown(): void
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
        $failMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputySurname":"%s","deputyPostcode":"%s"},"case_number_matches":null}';
        try {
            $this->casrecVerificationService->validate('WRONG678', 'CSurn', 'DSurn', 'DPC123');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($failMessage, 'WRONG678', 'CSurn', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
        }

        try {
            $this->assertTrue($this->casrecVerificationService->validate('11111111', 'WRONG', 'DSurn', 'DPC123'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($failMessage, '11111111', 'WRONG', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
        }

        try {
            $this->assertTrue($this->casrecVerificationService->validate('11111111', 'CSurn', 'WRONG', 'DPC123'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($failMessage, '11111111', 'CSurn', 'WRONG', 'DPC123'),
                $e->getMessage()
            );
        }

        try {
            $this->assertTrue($this->casrecVerificationService->validate('11111111', 'CSurn', 'DSurn', 'WRONG'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($failMessage, '11111111', 'CSurn', 'DSurn', 'WRONG'),
                $e->getMessage()
            );
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
        $this->assertTrue($this->casrecVerificationService->validate('33333333', 'CSurn', 'MLDUnique', 'MLD1AA'));
    }

    /**
     * @test
     */
    public function validateSameAddressMLDSiblings()
    {
        $this->assertTrue($this->casrecVerificationService->validate('33333333', 'CSurn', 'Sibling', 'MLD1BB'));
    }

    /**
     * @test
     */
    public function validateMLDSiblingsMissingPostcode()
    {
        // if all MLD postcodes are in casrec, the postcode check is run
        try {
            $this->assertTrue($this->casrecVerificationService->validate('11111111', 'CSurn', 'DSurn', 'DOEsnT MatteR'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('{"search_terms":{"caseNumber":"11111111","clientLastname":"CSurn","deputySurname":"DSurn","deputyPostcode":"DOEsnT MatteR"},"case_number_matches":null}', $e->getMessage());
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
        $this->casrecVerificationService->validate('33333333', 'CSurn', 'Sibling', 'MLD1BB');
        $this->assertEquals(['MLDB', 'MLDC'], $this->casrecVerificationService->getLastMatchedDeputyNumbers());
    }
}
