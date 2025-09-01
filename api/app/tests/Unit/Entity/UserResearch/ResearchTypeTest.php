<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\UserResearch;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use App\Entity\UserResearch\ResearchType;
use PHPUnit\Framework\TestCase;

final class ResearchTypeTest extends TestCase
{
    
    #[DataProvider('propertyProvider')]
    #[Test]
    public function constructSetsPropertiesBasedOnFormResponses(
        array $formResponses,
        ?bool $expectedSurveysValue,
        ?bool $expectedVideoCallValue,
        ?bool $expectedPhoneValue,
        ?bool $expectedInPersonValue
    ): void {
        $researchType = new ResearchType($formResponses);

        self::assertEquals($expectedSurveysValue, $researchType->getSurveys());
        self::assertEquals($expectedVideoCallValue, $researchType->getVideoCall());
        self::assertEquals($expectedPhoneValue, $researchType->getPhone());
        self::assertEquals($expectedInPersonValue, $researchType->getInPerson());
    }

    public static function propertyProvider(): array
    {
        return [
            'all set' => [['surveys', 'videoCall', 'phone', 'inPerson'], true, true, true, true],
            'surveys set' => [['surveys'], true, null, null, null],
            'videoCall set' => [['videoCall'], null, true, null, null],
            'phone set' => [['phone'], null, null, true, null],
            'inPerson set' => [['inPerson'], null, null, null, true],
        ];
    }

    #[Test]
    public function constructNoPropertiesThrowsException(): void
    {
        self::expectException(RuntimeException::class);
        new ResearchType([]);
    }
}
