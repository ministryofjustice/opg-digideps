<?php declare(strict_types=1);


namespace Tests\App\Entity\UserResearch;

use App\Entity\UserResearch\ResearchType;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class ResearchTypeTest extends TestCase
{
    /**
     * @dataProvider propertyProvider
     * @test
     */
    public function __construct_sets_properties_based_on_form_responses(
        array $formResponses,
        ?bool $expectedSurveysValue,
        ?bool $expectedVideoCallValue,
        ?bool $expectedPhoneValue,
        ?bool $expectedInPersonValue
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
    public function __construct_no_properties_throws_exception()
    {
        self::expectException(RuntimeException::class);
        new ResearchType([]);
    }
}
