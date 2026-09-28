<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\File\Verifier;

use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Service\File\Verifier\ConstraintVerifier;
use OPG\Digideps\Frontend\Service\File\Verifier\VerificationStatus;
use OPG\Digideps\Frontend\Service\File\Verifier\VerifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintVerifierTest extends TestCase
{
    private VerifierInterface $verifier;
    private ValidatorInterface&MockObject $validator;
    private Document $document;
    private VerificationStatus $result;

    public function setUp(): void
    {
        $this->validator = self::createMock(ValidatorInterface::class);
        $this->verifier = new ConstraintVerifier($this->validator);

        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->method('getClientOriginalName')->willReturn('file.txt');

        $this->document = new Document()->setFile($file);
    }

    public function testVerificationPassesWhenGivenValidDocument(): void
    {
        $this->ensureDocumentWillBeValid()
            ->invokeTest()
            ->assertStatusIsPassed();
    }

    public function testVerificationFailsWhenGivenInvalidDocument(): void
    {
        $this->ensureDocumentWillBeInvalid()
            ->invokeTest()
            ->assertStatusIsFailed();
    }

    private function ensureDocumentWillBeValid(): static
    {
        $this->validator->expects(self::once())
            ->method('validate')
            ->with($this->document, null, ['document'])
            ->willReturn(new ConstraintViolationList());

        return $this;
    }

    private function ensureDocumentWillBeInvalid(): static
    {
        $validationResult = new ConstraintViolationList();
        $validationResult->add($this->createMock(ConstraintViolationInterface::class));

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($this->document, null, ['document'])
            ->willReturn($validationResult);

        return $this;
    }

    private function invokeTest(): static
    {
        $this->result = $this->verifier->verify($this->document, new VerificationStatus());

        return $this;
    }

    private function assertStatusIsPassed(): void
    {
        self::assertEquals(VerificationStatus::PASSED, $this->result->getStatus());
        self::assertNull($this->result->getError());
    }

    private function assertStatusIsFailed(): void
    {
        self::assertEquals(VerificationStatus::FAILED, $this->result->getStatus());
        self::assertNotNull($this->result->getError());
    }
}
