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
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('DSurn')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('DPC123')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('Dep1')
            ->getMock();

        $crLayNoPC = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('22222222')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('DSurn')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        // Group MLD1 has a postcode for each of the three deputies
        $preRegMLD1A = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('MLDUnique')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1AA')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $preRegMLD1B = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1BB')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $preRegMLD1C = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1BB')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDC')
            ->getMock();

        // Group MLD2 has a missing postcode for one of the two deputies
        $preRegMLD2A = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD2AA')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $preRegMLD2B = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $singleLayDeputy = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('55555555')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('Smith')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Jones')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('ABC 123')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('10000001')
            ->getMock();

        $mockPreRegRepo = m::mock(PreRegistrationRepository::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findByCaseNumber')->with('11111111')->andReturn([$crLayHasPC])
            ->shouldReceive('findByCaseNumber')->with('22222222')->andReturn([$crLayNoPC])
            ->shouldReceive('findByCaseNumber')->with('33333333')->andReturn([$preRegMLD1A, $preRegMLD1B, $preRegMLD1C])
            ->shouldReceive('findByCaseNumber')->with('44444444')->andReturn([$preRegMLD2A, $preRegMLD2B])
            ->shouldReceive('findByCaseNumber')->with('55555555')->andReturn([$singleLayDeputy])
            ->shouldReceive('findByCaseNumber')->with('12345678')->andReturn([])
            ->shouldReceive('findByCaseNumber')->with('WRONG678')->andReturn([])

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
        $incorrectCaseNumberMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputySurname":"%s","deputyPostcode":"%s"}}';
        try {
            $this->preRegistrationVerificationService->validate('WRONG678', 'CSurn', 'DSurn', 'DPC123');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($incorrectCaseNumberMessage, 'WRONG678', 'CSurn', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
            $this->assertEquals(460, $e->getCode());
        }

        $incorrectClientLastnameMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputySurname":"%s","deputyPostcode":"%s"},"case_number_matches":null}';
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'WRONG', 'DSurn', 'DPC123'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($incorrectClientLastnameMessage, '11111111', 'WRONG', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
            $this->assertEquals(461, $e->getCode());
        }

        $incorrectClientLastnameMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputySurname":"%s","deputyPostcode":"%s"},"client_last_name_matches":null}';
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'WRONG', 'DPC123'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($incorrectClientLastnameMessage, '11111111', 'CSurn', 'WRONG', 'DPC123'),
                $e->getMessage()
            );
            $this->assertEquals(462, $e->getCode());
        }

        $incorrectPostCodeMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputySurname":"%s","deputyPostcode":"%s"},"deputy_last_name_matches":null}';
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DSurn', 'WRONG'));
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($incorrectPostCodeMessage, '11111111', 'CSurn', 'DSurn', 'WRONG'),
                $e->getMessage()
            );
            $this->assertEquals(400, $e->getCode());
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
            $this->assertStringContainsString('{"search_terms":{"caseNumber":"11111111","clientLastname":"CSurn","deputySurname":"DSurn","deputyPostcode":"DOEsnT MatteR"},"deputy_last_name_matches":null}', $e->getMessage());
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
