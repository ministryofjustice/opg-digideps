<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\Formatter;

use OPG\Digideps\Backend\EventListener\RestInputOutputFormatter;
use OPG\Digideps\Backend\Service\Formatter\RestFormatter;
use OPG\Digideps\Backend\Service\Validator\RestArrayValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class RestFormatterTest extends TestCase
{
    private RestInputOutputFormatter&MockObject $inputOutputFormatter;
    private RestArrayValidator&MockObject $validator;
    private RestFormatter $sut;

    public function setUp(): void
    {
        $this->inputOutputFormatter = self::createMock(RestInputOutputFormatter::class);
        $this->validator = self::createMock(RestArrayValidator::class);

        $this->sut = new RestFormatter(
            $this->inputOutputFormatter,
            $this->validator
        );
    }

    #[Test]
    public function deserializeBodyContent(): void
    {
        $incomingRequest = new Request();
        $expectedContentArray = ['aKey' => 'some data'];
        $this->inputOutputFormatter
            ->expects($this->once())
            ->method('requestContentToArray')
            ->with($incomingRequest)
            ->willReturn($expectedContentArray);

        $assertions = ['aDataKey' => 'someAssertion'];

        $this->validator->expects(self::once())
            ->method('validateArray')
            ->with($expectedContentArray, $assertions);

        $actualContentArray = $this->sut->deserializeBodyContent($incomingRequest, $assertions);

        self::assertEquals($expectedContentArray, $actualContentArray);
    }

    #[Test]
    public function setJmsSerialiserGroups(): void
    {
        $serialiserGroups = ['group1', 'group2'];

        $this->inputOutputFormatter
            ->expects($this->once())
            ->method('addContextModifier')
            ->with(self::isType('callable'));

        $this->sut->setJmsSerialiserGroups($serialiserGroups);
    }

    #[Test]
    public function validateArray(): void
    {
        $data = ['some' => 'data'];
        $assertions = ['some' => 'assertions'];
        $this->validator->expects(self::once())
            ->method('validateArray')
            ->with($data, $assertions);

        $this->sut->validateArray($data, $assertions);
    }
}
