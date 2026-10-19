<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Client\Internal;

use Faker\Factory;
use Faker\Generator;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Model\FeedbackReport;
use OPG\Digideps\Frontend\Service\Client\Internal\SatisfactionApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SatisfactionApiTest extends TestCase
{
    private Generator $faker;
    private RestClient&MockObject $restClient;
    private SatisfactionApi $sut;

    public function setUp(): void
    {
        $this->faker = Factory::create('en_UK');
        $this->restClient = self::createMock(RestClient::class);
        $this->sut = new SatisfactionApi($this->restClient);
    }

    public function testCreateGeneralFeedback(): void
    {
        $score = $this->faker->randomElement([1, 2, 3, 4, 5]);
        $comments = $this->faker->realText();

        $this->restClient->expects(self::once())
            ->method('post')
            ->with(
                'satisfaction/public',
                ['score' => $score, 'comments' => $comments]
            );

        $formData = [
            'comments' => $comments,
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'page' => $this->faker->url(),
            'email' => $this->faker->email(),
            'satisfactionLevel' => $score,
        ];

        $this->sut->createGeneralFeedback($formData);
    }

    /**
     * @dataProvider commentsProvider
     */
    public function testCreatePostSubmissionFeedback(
        ?string $comments,
        string $expectedCommentsInPostRequest,
        ?int $reportId
    ): void {
        $score = $this->faker->randomElement([1, 2, 3, 4, 5]);
        $reportType = $this->faker->randomElement([
            Report::TYPE_COMBINED_HIGH_ASSETS,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            Report::TYPE_ABBREVIATION_COMBINED,
            Report::TYPE_COMBINED_LOW_ASSETS,
            Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS,
            Report::TYPE_HEALTH_WELFARE,
        ]);

        $this->restClient->expects(self::once())
            ->method('post')
            ->with(
                'satisfaction',
                [
                    'score' => $score,
                    'comments' => $expectedCommentsInPostRequest,
                    'reportType' => $reportType,
                    'reportId' => $reportId,
                ]
            )
            ->willReturn(1);

        $feedbackReportObject = new FeedbackReport()
            ->setComments($comments)
            ->setSatisfactionLevel($score);

        $this->sut->createPostSubmissionFeedback($feedbackReportObject, $reportType, $reportId);
    }

    public static function commentsProvider(): array
    {
        return [
            'Comments included - Report' => ['Its greeeeat', 'Its greeeeat', 222],
            'Empty string comments' => ['', 'Not provided', 333],
        ];
    }
}
