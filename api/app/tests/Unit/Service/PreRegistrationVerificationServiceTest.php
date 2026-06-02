<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Repository\PreRegistrationRepository;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Service\PreRegistrationVerificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class PreRegistrationVerificationServiceTest extends TestCase
{
    private PreRegistrationVerificationService $preRegistrationVerificationService;

    private function makePreReg(
        string $caseNumber,
        string $clientLastname,
        string $deputySurname,
        string $deputyPostCode,
        string $deputyUid,
    ): PreRegistration&MockObject {
        $mock = $this->createMock(PreRegistration::class);
        $mock->method('getCaseNumber')->willReturn($caseNumber);
        $mock->method('getClientLastname')->willReturn($clientLastname);
        $mock->method('getDeputyFirstname')->willReturn('DFirs');
        $mock->method('getDeputySurname')->willReturn($deputySurname);
        $mock->method('getDeputyPostCode')->willReturn($deputyPostCode);
        $mock->method('getDeputyUid')->willReturn($deputyUid);

        return $mock;
    }

    public function setUp(): void
    {
        $crLayHasPC   = $this->makePreReg('11111111', 'CSurn', 'DSurn', 'DPC123', 'Dep1');
        $crLayNoPC    = $this->makePreReg('22222222', 'CSurn', 'DSurn', '', 'MLDA');
        $preRegMLD1A  = $this->makePreReg('33333333', 'CSurn', 'MLDUnique', 'MLD1AA', 'MLDA');
        $preRegMLD1B  = $this->makePreReg('33333333', 'CSurn', 'Sibling', 'MLD1BB', 'MLDB');
        $preRegMLD1C  = $this->makePreReg('33333333', 'CSurn', 'Sibling', 'MLD1BB', 'MLDC');
        $preRegMLD2A  = $this->makePreReg('44444444', 'CSurn', 'Sibling', 'MLD2AA', 'MLDA');
        $preRegMLD2B  = $this->makePreReg('44444444', 'CSurn', 'Sibling', '', 'MLDB');
        $singleLayDep = $this->makePreReg('55555555', 'Smith', 'Jones', 'ABC 123', '10000001');

        $mockPreRegRepo = $this->createMock(PreRegistrationRepository::class);
        $mockPreRegRepo->method('findByCaseNumber')->willReturnMap([
            ['11111111', [$crLayHasPC]],
            ['22222222', [$crLayNoPC]],
            ['33333333', [$preRegMLD1A, $preRegMLD1B, $preRegMLD1C]],
            ['44444444', [$preRegMLD2A, $preRegMLD2B]],
            ['55555555', [$singleLayDep]],
            ['12345678', []],
            ['WRONG678', []],
        ]);

        $mockUserRepo = $this->createMock(UserRepository::class);
        $serializer   = $this->createMock(SerializerInterface::class);

        $this->preRegistrationVerificationService = new PreRegistrationVerificationService($serializer, $mockPreRegRepo, $mockUserRepo);
    }


    public function testIsMultiDeputyCase(): void
    {
        $this->assertFalse($this->preRegistrationVerificationService->isMultiDeputyCase('11111111'));
        $this->assertTrue($this->preRegistrationVerificationService->isMultiDeputyCase('33333333'));
        $this->assertTrue($this->preRegistrationVerificationService->isMultiDeputyCase('44444444'));
    }

    public function testValidateCaseInsensitive(): void
    {
        $this->assertCount(1, $this->preRegistrationVerificationService->validate('11111111', 'csurn', 'DFIRS', 'DSURN', 'dPc123'));
    }

    public function testValidateNonMLDWithPostcode(): void
    {
        $this->assertCount(1, $this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'Dfirs', 'DSurn', 'DPC123'));

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
            $this->assertCount(1, $this->preRegistrationVerificationService->validate('11111111', 'WRONG', 'DFirs', 'DSurn', 'DPC123'));
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
            $this->assertCount(1, $this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'WRONG', 'DSurn', 'DPC123'));
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
            $this->assertCount(1, $this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DFirs', 'WRONG', 'DPC123'));
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
            $this->assertCount(1, $this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DFirs', 'DSurn', 'WRONG'));
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
            $this->assertCount(1, $this->preRegistrationVerificationService->validate('11111111', 'WRONG', 'WRONG', 'WRONG', 'WRONG'));
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                sprintf($termsAndMatchesMessage, '11111111', 'WRONG', 'WRONG', 'WRONG', 'WRONG'),
                $e->getMessage()
            );
            $this->assertStringContainsString($incorrectNamesAndPostcode, $e->getMessage());
            $this->assertEquals(461, $e->getCode());
        }
    }

    public function testValidateNonMLDWithNoPostcode(): void
    {
        $this->assertCount(1, $this->preRegistrationVerificationService->validate('22222222', 'CSurn', 'DFirs', 'DSurn', ''));
    }

    public function testValidateMLDUnique(): void
    {
        $this->assertCount(1, $this->preRegistrationVerificationService->validate('33333333', 'CSurn', 'DFirs', 'MLDUnique', 'MLD1AA'));
    }

    public function testValidateSameAddressMLDSiblings(): void
    {
        $this->assertCount(2, $this->preRegistrationVerificationService->validate('33333333', 'CSurn', 'DFirs', 'Sibling', 'MLD1BB'));
    }

    public function testValidateMLDSiblingsMissingPostcode(): void
    {
        // if all MLD postcodes are in preRegistration, the postcode check is run
        try {
            $this->assertCount(1, $this->preRegistrationVerificationService->validate('11111111', 'CSurn', 'DFirs', 'DSurn', 'DOEsnT MatteR'));
        } catch (\RuntimeException $e) {
            $expectedErrorMessage = '{"search_terms":{"caseNumber":"%s","clientLastname":"%s","deputyFirstname":"%s","deputyLastname":"%s","deputyPostcode":"%s"},"case_number_matches":null,"matching_errors":{"client_lastname":false,"deputy_firstname":false,"deputy_lastname":false,"deputy_postcode":true}}';
            $this->assertStringContainsString(
                sprintf($expectedErrorMessage, '11111111', 'CSurn', 'DFirs', 'DSurn', 'DOEsnT MatteR'),
                $e->getMessage()
            );
            $this->assertEquals(461, $e->getCode());
        }

        // but if one MLD in preRegistration, the postcode check is skipped
        $this->assertCount(2, $this->preRegistrationVerificationService->validate('44444444', 'CSurn', 'DFirs', 'Sibling', 'DOEsnT MatteR'));
    }
}
