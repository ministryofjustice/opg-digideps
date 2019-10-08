<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompositeVerifierTest extends TestCase
{
    /** @var VerifierInterface */
    private $verifier;

    /** @var VerifierInterface | MockObject */
    private $alphaVerifier;

    /** @var VerifierInterface | MockObject */
    private $betaVerifier;

    /** @var VerifierInterface | MockObject */
    private $charlieVerifier;

    /** @var Document */
    private $document;

    /** @var Form | MockObject */
    private $form;

    /** @var bool */
    private $result;

    public function setUp(): void
    {
        $this->alphaVerifier = $this->createMock(VerifierInterface::class);
        $this->betaVerifier = $this->createMock(VerifierInterface::class);
        $this->charlieVerifier = $this->createMock(VerifierInterface::class);

        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->method('getClientOriginalName')->willReturn('file.txt');
        $this->document = (new Document())->setFile($file);
        $this->form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();

        $this->verifier = (new CompositeVerifier())
            ->addVerifier($this->alphaVerifier)
            ->addVerifier($this->betaVerifier)
            ->addVerifier($this->charlieVerifier);
    }

    /**
     * @test
     */
    public function returnsTrueWhenAllChildVerifiersReturnTrue()
    {
        $this
            ->ensureAllVerifiersReturnTrue()
            ->invokeTest()
            ->assertResultIsTrue();
    }

    /**
     * @test
     */
    public function returnsFalseWhenChildVerifierReturnsFalse()
    {
        $this
            ->ensureOneVerifierReturnsFalse()
            ->assertNoRemainingVerifiersWillBeInvoked()
            ->invokeTest()
            ->assertResultIsFalse();
    }

    /**
     * @return CompositeVerifierTest
     */
    private function ensureAllVerifiersReturnTrue(): CompositeVerifierTest
    {
        $this->alphaVerifier->expects($this->once())->method('verify')->with($this->document, $this->form)->willReturn(true);
        $this->betaVerifier->expects($this->once())->method('verify')->with($this->document, $this->form)->willReturn(true);
        $this->charlieVerifier->expects($this->once())->method('verify')->with($this->document, $this->form)->willReturn(true);

        return $this;
    }

    /**
     * @return CompositeVerifierTest
     */
    private function ensureOneVerifierReturnsFalse(): CompositeVerifierTest
    {
        $this->alphaVerifier->expects($this->once())->method('verify')->with($this->document, $this->form)->willReturn(true);
        $this->betaVerifier->expects($this->once())->method('verify')->with($this->document, $this->form)->willReturn(false);

        return $this;
    }

    /**
     * @return CompositeVerifierTest
     */
    private function assertNoRemainingVerifiersWillBeInvoked(): CompositeVerifierTest
    {
        $this->charlieVerifier->expects($this->never())->method('verify')->with($this->document, $this->form);

        return $this;
    }

    /**
     * @return CompositeVerifierTest
     */
    private function invokeTest(): CompositeVerifierTest
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
