<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Formatter;

use PHPUnit\Framework\Attributes\Test;
use App\EventListener\RestInputOuputFormatter;
use App\Service\Formatter\RestFormatter;
use App\Service\Validator\RestArrayValidator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;

final class RestFormatterTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $inputOutputFormatter;
    private ObjectProphecy $validator;
    private RestFormatter $sut;

    public function setUp(): void
    {
        $this->inputOutputFormatter = self::prophesize(RestInputOuputFormatter::class);
        $this->validator = self::prophesize(RestArrayValidator::class);

        $this->sut = new RestFormatter(
            $this->inputOutputFormatter->reveal(),
            $this->validator->reveal()
        );
    }

    #[Test]
    public function deserializeBodyContent(): void
    {
        $incomingRequest = new Request();
        $expectedContentArray = ['aKey' => 'some data'];
        $this->inputOutputFormatter
            ->requestContentToArray($incomingRequest)
            ->shouldBeCalled()
            ->willReturn($expectedContentArray);

        $assertions = ['aDataKey' => 'someAssertion'];

        $this->validator->validateArray($expectedContentArray, $assertions)->shouldBeCalled();

        $actualContentArray = $this->sut->deserializeBodyContent($incomingRequest, $assertions);

        self::assertEquals($expectedContentArray, $actualContentArray);
    }

    #[Test]
    public function setJmsSerialiserGroups(): void
    {
        $serialiserGroups = ['group1', 'group2'];

        $this->inputOutputFormatter
            ->addContextModifier(Argument::type('Callable'))
            ->shouldBeCalled();

        $this->sut->setJmsSerialiserGroups($serialiserGroups);
    }

    #[Test]
    public function validateArray(): void
    {
        $data = ['some' => 'data'];
        $assertions = ['some' => 'assertions'];
        $this->validator->validateArray($data, $assertions)->shouldBeCalled();

        $this->sut->validateArray($data, $assertions);
    }
}
