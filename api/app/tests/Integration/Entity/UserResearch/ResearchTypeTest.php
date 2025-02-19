<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\UserResearch;

use App\Entity\UserResearch\ResearchType;
use PHPUnit\Framework\TestCase;

class ResearchTypeTest extends TestCase
{
    /**
     * @dataProvider propertyProvider
     *
     * @test
     */
    public function constructSetsPropertiesBasedOnFormResponses(
        array $formResponses,
        ?bool $expectedSurveysValue,
        ?bool $expectedVideoCallValue,
        ?bool $expectedPhoneValue,
        ?bool $expectedInPersonValue,
    ) {
        $researchType = new ResearchType($formResponses);

        self::assertEquals($expectedSurveysValue, $researchType->getSurveys());
        self::assertEquals($expectedVideoCallValue, $researchType->getVideoCall());
        self::assertEquals($expectedPhoneValue, $researchType->getPhone());
        self::assertEquals($expectedInPersonValue, $researchType->getInPerson());
    }

    public function propertyProvider()
    {
        return [
            'all set' => [['surveys', 'videoCall', 'phone', 'inPerson'], true, true, true, true],
            'surveys set' => [['surveys'], true, null, null, null],
            'videoCall set' => [['videoCall'], null, true, null, null],
            'phone set' => [['phone'], null, null, true, null],
            'inPerson set' => [['inPerson'], null, null, null, true],
        ];
    }

    /** @test */
    public function constructNoPropertiesThrowsException()
    {
        self::expectException(\RuntimeException::class);
        new ResearchType([]);
    }
}
