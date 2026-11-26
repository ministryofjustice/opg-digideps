<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\Formatter;

use OPG\Digideps\Backend\EventListener\RestInputOutputFormatter;
use OPG\Digideps\Backend\Service\Formatter\RestFormatter;
use OPG\Digideps\Backend\Service\Validator\RestArrayValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class RestFormatterTest extends TestCase
{
    #[Test]
    public function deserializeBodyContent(): void
    {
        $inputOutputFormatter = self::createMock(RestInputOutputFormatter::class);
        $validator = self::createMock(RestArrayValidator::class);

        $sut = new RestFormatter(
            $inputOutputFormatter,
            $validator
        );

        $incomingRequest = new Request();
        $expectedContentArray = ['aKey' => 'some data'];
        $inputOutputFormatter
            ->expects($this->once())
            ->method('requestContentToArray')
            ->with($incomingRequest)
            ->willReturn($expectedContentArray);

        $assertions = ['aDataKey' => 'someAssertion'];

        $validator->expects(self::once())
            ->method('validateArray')
            ->with($expectedContentArray, $assertions);

        $actualContentArray = $sut->deserializeBodyContent($incomingRequest, $assertions);

        self::assertEquals($expectedContentArray, $actualContentArray);
    }

    #[Test]
    public function setJmsSerialiserGroups(): void
    {
        $inputOutputFormatter = self::createMock(RestInputOutputFormatter::class);
        $validator = self::createStub(RestArrayValidator::class);

        $sut = new RestFormatter(
            $inputOutputFormatter,
            $validator
        );

        $serialiserGroups = ['group1', 'group2'];

        $inputOutputFormatter
            ->expects($this->once())
            ->method('addContextModifier')
            ->with($this->isCallable());

        $sut->setJmsSerialiserGroups($serialiserGroups);
    }

    #[Test]
    public function validateArray(): void
    {
        $inputOutputFormatter = self::createStub(RestInputOutputFormatter::class);
        $validator = self::createMock(RestArrayValidator::class);

        $sut = new RestFormatter(
            $inputOutputFormatter,
            $validator
        );

        $data = ['some' => 'data'];
        $assertions = ['some' => 'assertions'];
        $validator->expects(self::once())
            ->method('validateArray')
            ->with($data, $assertions);

        $sut->validateArray($data, $assertions);
    }
}
