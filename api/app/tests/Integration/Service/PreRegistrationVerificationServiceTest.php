<?php

namespace App\Tests\Unit\Service;

use App\Repository\PreRegistrationRepository;
use App\Repository\UserRepository;
use App\Service\PreRegistrationVerificationService;
use Mockery as m;
use Prophecy\PhpUnit\ProphecyTrait;
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
            ->shouldReceive('getDeputyFirstname')->withNoArgs()->andReturn('DFirs')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('DSurn')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('DPC123')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('Dep1')
            ->getMock();

        $crLayNoPC = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('22222222')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputyFirstname')->withNoArgs()->andReturn('DFirs')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('DSurn')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        // Group MLD1 has a postcode for each of the three deputies
        $preRegMLD1A = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputyFirstname')->withNoArgs()->andReturn('DFirs')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('MLDUnique')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1AA')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $preRegMLD1B = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputyFirstname')->withNoArgs()->andReturn('DFirs')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1BB')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $preRegMLD1C = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('33333333')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputyFirstname')->withNoArgs()->andReturn('DFirs')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD1BB')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDC')
            ->getMock();

        // Group MLD2 has a missing postcode for one of the two deputies
        $preRegMLD2A = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputyFirstname')->withNoArgs()->andReturn('DFirs')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('MLD2AA')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDA')
            ->getMock();

        $preRegMLD2B = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('44444444')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('CSurn')
            ->shouldReceive('getDeputyPostCode')->withNoArgs()->andReturn('')
            ->shouldReceive('getDeputyFirstname')->withNoArgs()->andReturn('DFirs')
            ->shouldReceive('getDeputySurname')->withNoArgs()->andReturn('Sibling')
            ->shouldReceive('getDeputyUid')->withNoArgs()->andReturn('MLDB')
            ->getMock();

        $singleLayDeputy = m::mock('\App\Entity\PreRegistration')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->withNoArgs()->andReturn('55555555')
            ->shouldReceive('getClientLastname')->withNoArgs()->andReturn('Smith')
            ->shouldReceive('getDeputyFirstname')->withNoArgs()->andReturn('DFirs')
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

        $mockUserRepo = m::mock(UserRepository::class);

        $serializer = self::prophesize(SerializerInterface::class);

        $this->preRegistrationVerificationService = new PreRegistrationVerificationService($serializer->reveal(), $mockPreRegRepo, $mockUserRepo);
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
    public function validateCaseInsensitive()
    {
        $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'csurn', 'DFIRS', 'DSURN', 'dPc123'));
    }

    /**
     * @test
     */
    public function validateNonMLDWithPostcode()
    {
        $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'Dfirs', 'DSurn', 'DPC123'));

        // test each fail individually
        $incorrectCaseNumberMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputyFirstname":"%s","deputyLastname":"%s","deputyPostcode":"%s"}}';
        try {
            $this->preRegistrationVerificationService->validate('WRONG678', 'CSurn', 'DFirs', 'DSurn', 'DPC123');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($incorrectCaseNumberMessage, 'WRONG678', 'CSurn', 'DFirs', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
            $this->assertEquals(460, $e->getCode());
        }

        $termsAndMatchesMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputyFirstname":"%s","deputyLastname":"%s","deputyPostcode":"%s"},"case_number_matches":null';

        $incorrectClientLastnameMessage = '"matching_errors":{"client_lastname":true,"deputy_firstname":false,"deputy_lastname":false,"deputy_postcode":false}';
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'WRONG', 'DFirs', 'DSurn', 'DPC123'));
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($termsAndMatchesMessage, '11111111', 'WRONG', 'DFirs', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
            $this->assertStringContainsString($incorrectClientLastnameMessage, $e->getMessage());
            $this->assertEquals(461, $e->getCode());
        }

        $incorrectDeputyFirstnameMessage = '"matching_errors":{"client_lastname":false,"deputy_firstname":true,"deputy_lastname":false,"deputy_postcode":false}';
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'WRONG', 'DSurn', 'DPC123'));
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($termsAndMatchesMessage, '11111111', 'CSurn', 'WRONG', 'DSurn', 'DPC123'),
                $e->getMessage()
            );
            $this->assertStringContainsString($incorrectDeputyFirstnameMessage, $e->getMessage());
            $this->assertEquals(461, $e->getCode());
        }

        $incorrectDeputyLastnameMessage = '"matching_errors":{"client_lastname":false,"deputy_firstname":false,"deputy_lastname":true,"deputy_postcode":false}';
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DFirs', 'WRONG', 'DPC123'));
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($termsAndMatchesMessage, '11111111', 'CSurn', 'DFirs', 'WRONG', 'DPC123'),
                $e->getMessage()
            );
            $this->assertStringContainsString($incorrectDeputyLastnameMessage, $e->getMessage());
            $this->assertEquals(461, $e->getCode());
        }

        $incorrectPostCodeMessage = '"matching_errors":{"client_lastname":false,"deputy_firstname":false,"deputy_lastname":false,"deputy_postcode":true}';
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DFirs', 'DSurn', 'WRONG'));
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($termsAndMatchesMessage, '11111111', 'CSurn', 'DFirs', 'DSurn', 'WRONG'),
                $e->getMessage()
            );
            $this->assertStringContainsString($incorrectPostCodeMessage, $e->getMessage());
            $this->assertEquals(461, $e->getCode());
        }

        $incorrectNamesAndPostcode = '"matching_errors":{"client_lastname":true,"deputy_firstname":true,"deputy_lastname":true,"deputy_postcode":true}';
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'WRONG', 'WRONG', 'WRONG', 'WRONG'));
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($termsAndMatchesMessage, '11111111', 'WRONG', 'WRONG', 'WRONG', 'WRONG'),
                $e->getMessage()
            );
            $this->assertStringContainsString($incorrectNamesAndPostcode, $e->getMessage());
            $this->assertEquals(461, $e->getCode());
        }
    }

    /**
     * @test
     */
    public function validateNonMLDWithNoPostcode()
    {
        $this->assertTrue($this->preRegistrationVerificationService->validate('22222222', 'CSurn', 'DFirs', 'DSurn', ''));
    }

    /**
     * @test
     */
    public function validateMLDUnique()
    {
        $this->assertTrue($this->preRegistrationVerificationService->validate('33333333', 'CSurn', 'DFirs', 'MLDUnique', 'MLD1AA'));
    }

    /**
     * @test
     */
    public function validateSameAddressMLDSiblings()
    {
        $this->assertTrue($this->preRegistrationVerificationService->validate('33333333', 'CSurn', 'DFirs', 'Sibling', 'MLD1BB'));
    }

    /**
     * @test
     */
    public function validateMLDSiblingsMissingPostcode()
    {
        // if all MLD postcodes are in preRegistration, the postcode check is run
        try {
            $this->assertTrue($this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DFirs', 'DSurn', 'DOEsnT MatteR'));
        } catch (\RuntimeException $e) {
            $expectedErrorMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputyFirstname":"%s","deputyLastname":"%s","deputyPostcode":"%s"},"case_number_matches":null,"matching_errors":{"client_lastname":false,"deputy_firstname":false,"deputy_lastname":false,"deputy_postcode":true}}';
            $this->assertStringContainsString(
                sprintf($expectedErrorMessage, '11111111', 'CSurn', 'DFirs', 'DSurn', 'DOEsnT MatteR'),
                $e->getMessage()
            );
            $this->assertEquals(461, $e->getCode());
        }

        // but if one MLD in preRegistration, the postcode check is skipped
        $this->assertTrue($this->preRegistrationVerificationService->validate('44444444', 'CSurn', 'DFirs', 'Sibling', 'DOEsnT MatteR'));
    }

    /**
     * @test
     */
    public function getLastMatchedDeputyNumbers()
    {
        $this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DFirs', 'DSurn', 'DPC123');
        $this->assertEquals(['Dep1'], $this->preRegistrationVerificationService->getLastMatchedDeputyNumbers());
        $this->preRegistrationVerificationService->validate('33333333', 'CSurn', 'DFirs', 'Sibling', 'MLD1BB');
        $this->assertEquals(['MLDB', 'MLDC'], $this->preRegistrationVerificationService->getLastMatchedDeputyNumbers());
    }
}
