<?php

declare(strict_types=1);

namespace DigidepsTests\Service\Client\Internal;

use App\Entity\Report\Report;
use App\Event\GeneralFeedbackSubmittedEvent;
use App\Event\PostSubmissionFeedbackSubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\FeedbackReport;
use App\Service\Client\Internal\SatisfactionApi;
use App\Service\Client\RestClient;
use App\TestHelpers\UserHelpers;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class SatisfactionApiTest extends TestCase
{
    use ProphecyTrait;

    /** @var Generator */
    private $faker;

    /** @var RestClient&ObjectProphecy */
    private $restClient;

    /** @var ObservableEventDispatcher&ObjectProphecy */
    private $eventDisaptcher;

    /** @var SatisfactionApi */
    private $sut;

    public function setUp(): void
    {
        $this->faker = Factory::create('en_UK');
        $this->restClient = self::prophesize(RestClient::class);
        $this->eventDisaptcher = self::prophesize(ObservableEventDispatcher::class);
        $this->sut = new SatisfactionApi($this->restClient->reveal(), $this->eventDisaptcher->reveal());
    }

    /**
     * @test
     */
    public function createGeneralFeedback()
    {
        $score = $this->faker->randomElement([1, 2, 3, 4, 5]);
        $comments = $this->faker->realText();

        $this->restClient->post(
            'satisfaction/public',
            ['score' => $score, 'comments' => $comments]
        )->shouldBeCalled();

        $formData = [
            'comments' => $comments,
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'page' => $this->faker->url(),
            'email' => $this->faker->email(),
            'satisfactionLevel' => $score,
        ];

        $event = (new GeneralFeedbackSubmittedEvent())->setFeedbackFormResponse($formData);
        $this->eventDisaptcher->dispatch($event, 'general.feedback.submitted')->shouldBeCalled();

        $this->sut->createGeneralFeedback($formData);
    }

    /**
     * @test
     * @dataProvider commentsProvider
     */
    public function createPostSubmissionFeedback(?string $comments, string $expectedCommentsInPostRequest, ?int $reportId, ?int $ndrId)
    {
        $score = $this->faker->randomElement([1, 2, 3, 4, 5]);
        $submittedByUser = UserHelpers::createUser();
        $reportType = $this->faker->randomElement([
            Report::TYPE_COMBINED_HIGH_ASSETS,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            Report::TYPE_ABBREVIATION_COMBINED,
            Report::TYPE_COMBINED_LOW_ASSETS,
            Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS,
            Report::TYPE_HEALTH_WELFARE,
        ]);

        $this->restClient->post(
            'satisfaction',
            [
                'score' => $score,
                'comments' => $expectedCommentsInPostRequest,
                'reportType' => $reportType,
                'reportId' => $reportId,
                'ndrId' => $ndrId,
            ]
        )
            ->shouldBeCalled()
            ->willReturn(1);

        $feedbackReportObject = (new FeedbackReport())
            ->setComments($comments)
            ->setSatisfactionLevel($score);

        $event = new PostSubmissionFeedbackSubmittedEvent($feedbackReportObject, $submittedByUser);
        $this->eventDisaptcher->dispatch($event, 'post.submission.feedback.submitted')->shouldBeCalled();

        $this->sut->createPostSubmissionFeedback($feedbackReportObject, $reportType, $submittedByUser, $reportId, $ndrId);
    }

    public function commentsProvider()
    {
        return [
            'Comments included - Report' => ['Its greeeeat', 'Its greeeeat', 222, null],
            'Comments included - NDR' => ['Its greeeeat', 'Its greeeeat', null, 333],
            'Empty string comments' => ['', 'Not provided', null, 333],
        ];
    }
}
