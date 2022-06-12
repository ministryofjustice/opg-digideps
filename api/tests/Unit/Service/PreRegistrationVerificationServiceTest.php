<?php

namespace App\Tests\Unit\Service;

use App\Repository\PreRegistrationRepository;
use App\Service\PreRegistrationVerificationService;
use Mockery as m;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class PreRegistrationVerificationServiceTest extends WebTestCase
{
    use ProphecyTrait;

    private PreRegistrationVerificationService $preRegistrationVerificationService;

    public static function setUpBeforeClass(): void
    {
        static::createClient(['environment' => 'test', 'debug' => false]);
    }

    public function setUp(): void
    {
        $crLayHasPC = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('11111111')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('DPC123')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('Dep1')
            ->getMock();

        $crLayNoPC = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('22222222')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        //Group MLD1 has a postcode for each of the three deputies
        $preRegMLD1A = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1AA')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('unique')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $preRegMLD1B = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1BB')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $preRegMLD1C = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1BB')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDC')
            ->getMock();

        //Group MLD2 has a missing postcode for one of the two deputies
        $preRegMLD2A = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD2AA')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $preRegMLD2B = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $mockPreRegRepo = m::mock(PreRegistrationRepository::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findByCaseNumber')->with('11111111')->andReturn([$crLayHasPC])
            ->shouldReceive('findByCaseNumber')->with('22222222')->andReturn([$crLayNoPC])
            ->shouldReceive('findByCaseNumber')->with('33333333')->andReturn([$preRegMLD1A, $preRegMLD1B, $preRegMLD1C])
            ->shouldReceive('findByCaseNumber')->with('44444444')->andReturn([$preRegMLD2A, $preRegMLD2B])
            ->shouldReceive('findByRegistrationDetails')->with('11111111', 'CSurn', 'DSurn')->andReturn([$crLayHasPC])
            ->shouldReceive('findByRegistrationDetails')->with('WRONG678', 'CSurn', 'DSurn')->andReturn([])
            ->shouldReceive('findByRegistrationDetails')->with('11111111', 'WRONG', 'DSurn')->andReturn([])
            ->shouldReceive('findByRegistrationDetails')->with('11111111', 'CSurn', 'WRONG')->andReturn([])
            ->shouldReceive('findByRegistrationDetails')->with('22222222', 'CSurn', 'DSurn')->andReturn([$crLayNoPC])
            ->shouldReceive('findByRegistrationDetails')->with('33333333', 'CSurn', 'MLDUnique')->andReturn([$preRegMLD1A])
            ->shouldReceive('findByRegistrationDetails')->with('33333333', 'CSurn', 'Sibling')->andReturn([$preRegMLD1B, $preRegMLD1C])
            ->shouldReceive('findByRegistrationDetails')->with('44444444', 'CSurn', 'Sibling')->andReturn([$preRegMLD2A, $preRegMLD2B])
            ->getMock();

        $serializer = self::prophesize(SerializerInterface::class);

        $this->preRegistrationVerificationService = new PreRegistrationVerificationService($serializer->reveal(), $mockPreRegRepo);
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
        $this->assertFalse($this->preRegistrationVerificationService->isMultiDeputyCase('11111111'));
        $this->assertTrue($this->preRegistrationVerificationService->isMultiDeputyCase('33333333'));
        $this->assertTrue($this->preRegistrationVerificationService->isMultiDeputyCase('44444444'));
    }

    /**
     * @test
     */
    public function validateNonMLDWithPostcode()
    {
        $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DSurn', 'DPC123'));

        // test each fail individually
        $failMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputySurname":"%s","deputyPostcode":"%s"},"case_number_matches":null}';
        try {
            $this->preRegistrationVerificationService->validate('WRONG678', 'CSurn', 'DSurn', 'DPC123');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($failMessage, 'WRONG678', 'CSurn', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
        }

        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'WRONG', 'DSurn', 'DPC123'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($failMessage, '11111111', 'WRONG', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
        }

        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'WRONG', 'DPC123'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($failMessage, '11111111', 'CSurn', 'WRONG', 'DPC123'),
                $e->getMessage()
            );
        }

        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DSurn', 'WRONG'));
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
        $this->assertTrue($this->preRegistrationVerificationService->validate('22222222', 'CSurn', 'DSurn', 'ANY ThinG'));
    }

    /**
     * @test
     */
    public function validateMLDUnique()
    {
        $this->assertTrue($this->preRegistrationVerificationService->validate('33333333', 'CSurn', 'MLDUnique', 'MLD1AA'));
    }

    /**
     * @test
     */
    public function validateSameAddressMLDSiblings()
    {
        $this->assertTrue($this->preRegistrationVerificationService->validate('33333333', 'CSurn', 'Sibling', 'MLD1BB'));
    }

    /**
     * @test
     */
    public function validateMLDSiblingsMissingPostcode()
    {
        // if all MLD postcodes are in preRegistration, the postcode check is run
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DSurn', 'DOEsnT MatteR'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('{"search_terms":{"caseNumber":"11111111","clientLastname":"CSurn","deputySurname":"DSurn","deputyPostcode":"DOEsnT MatteR"},"case_number_matches":null}', $e->getMessage());
        }

        // but if one MLD in preRegistration, the postcode check is skipped
        $this->assertTrue($this->preRegistrationVerificationService->validate('44444444', 'CSurn', 'Sibling', 'DOEsnT MatteR'));
    }

    /**
     * @test
     */
    public function getLastMatchedDeputyNumbers()
    {
        $this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DSurn', 'DPC123');
        $this->assertEquals(['Dep1'], $this->preRegistrationVerificationService->getLastMatchedDeputyNumbers());
        $this->preRegistrationVerificationService->validate('33333333', 'CSurn', 'Sibling', 'MLD1BB');
        $this->assertEquals(['MLDB', 'MLDC'], $this->preRegistrationVerificationService->getLastMatchedDeputyNumbers());
    }
}
