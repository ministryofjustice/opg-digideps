<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintVerifierTest extends TestCase
{
    /** @var VerifierInterface */
    private $verifier;

    /** @var ValidatorInterface | MockObject */
    private $validator;

    /** @var Document */
    private $document;

    /** @var VerificationStatus */
    private $result;

    public function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->verifier = new ConstraintVerifier($this->validator);

        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->method('getClientOriginalName')->willReturn('file.txt');
        $this->document = (new Document())->setFile($file);
    }


    /**
     * @test
     */
    public function verificationPassesWhenGivenValidDocument()
    {
        $this
            ->ensureDocumentWillBeValid()
            ->invokeTest()
            ->assertStatusIsPassed();
    }

    /**
     * @test
     */
    public function verificationFailsWhenGivenInvalidDocument()
    {
        $this
            ->ensureDocumentWillBeInvalid()
            ->invokeTest()
            ->assertStatusIsFailed();
    }

    /**
     * @return ConstraintVerifierTest
     */
    private function ensureDocumentWillBeValid(): ConstraintVerifierTest
    {
        $this
            ->validator
            ->expects($this->once())
            ->method('validate')
            ->with($this->document, null, ['document'])
            ->willReturn(new ConstraintViolationList());

        return $this;
    }

    /**
     * @return ConstraintVerifierTest
     */
    private function ensureDocumentWillBeInvalid(): ConstraintVerifierTest
    {
        $validationResult = new ConstraintViolationList();
        $validationResult->add($this->createMock(ConstraintViolationInterface::class));

        $this
            ->validator
            ->expects($this->once())
            ->method('validate')
            ->with($this->document, null, ['document'])
            ->willReturn($validationResult);

        return $this;
    }

    /**
     * @return ConstraintVerifierTest
     */
    private function invokeTest(): ConstraintVerifierTest
    {
        $this->result = $this->verifier->verify($this->document, new VerificationStatus());

        return $this;
    }

    private function assertStatusIsPassed(): void
    {
        $this->assertEquals(VerificationStatus::PASSED, $this->result->getStatus());
        $this->assertNull($this->result->getError());
    }

    private function assertStatusIsFailed(): void
    {
        $this->assertEquals(VerificationStatus::FAILED, $this->result->getStatus());
        $this->assertNotNull($this->result->getError());
    }
}
