<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
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

    /** @var Form | MockObject */
    private $form;

    /** @var bool */
    private $result;

    public function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->verifier = new ConstraintVerifier($this->validator);

        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->method('getClientOriginalName')->willReturn('file.txt');
        $this->document = (new Document())->setFile($file);
        $this->form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
    }


    /**
     * @test
     */
    public function returnsTrueWhenGivenValidDocument()
    {
        $this
            ->ensureDocumentWillBeValid()
            ->invokeTest()
            ->assertResultIsTrue();
    }

    /**
     * @test
     */
    public function returnsFalseWhenGivenInvalidDocument()
    {
        $this
            ->ensureDocumentWillBeInvalid()
            ->assertErrorsWillBeAddedToForm()
            ->invokeTest()
            ->assertResultIsFalse();
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
    private function assertErrorsWillBeAddedToForm(): ConstraintVerifierTest
    {
        $childForm = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();

        $this->form->method('get')->with('files')->willReturn($childForm);
        $childForm->expects($this->once())->method('addError')->with($this->isInstanceOf(FormError::class));

        return $this;
    }

    /**
     * @return ConstraintVerifierTest
     */
    private function invokeTest(): ConstraintVerifierTest
    {
        $this->result = $this->verifier->verify($this->document, $this->form);

        return $this;
    }

    private function assertResultIsTrue(): void
    {
        $this->assertTrue($this->result);
    }

    private function assertResultIsFalse(): void
    {
        $this->assertFalse($this->result);
    }
}
