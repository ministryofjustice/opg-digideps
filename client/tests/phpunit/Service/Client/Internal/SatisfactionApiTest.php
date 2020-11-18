<?php declare(strict_types=1);


namespace DigidepsTests\Service\Client\Internal;

use AppBundle\Entity\Report\Report;
use AppBundle\Event\GeneralFeedbackSubmittedEvent;
use AppBundle\Event\PostSubmissionFeedbackSubmittedEvent;
use AppBundle\Model\FeedbackReport;
use AppBundle\Service\Client\Internal\SatisfactionApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\TestHelpers\UserHelpers;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SatisfactionApiTest extends TestCase
{
    /** @var \Faker\Generator */
    private $faker;

    /** @var RestClient&ObjectProphecy */
    private $restClient;

    /** @var MailFactory&ObjectProphecy */
    private $mailFactory;

    /** @var MailSender&ObjectProphecy */
    private $mailSender;

    /** @var EventDispatcher&ObjectProphecy */
    private $eventDisaptcher;

    /**  @var SatisfactionApi */
    private $sut;

    public function setUp(): void
    {
        $this->faker = Factory::create('en_UK');
        $this->restClient = self::prophesize(RestClient::class);
        $this->eventDisaptcher = self::prophesize(EventDispatcher::class);
        $this->sut = new SatisfactionApi($this->restClient->reveal(), $this->eventDisaptcher->reveal());
    }

    /**
     * @test
     */
    public function createGeneralFeedback()
    {
        $score = $this->faker->randomElement([1,2,3,4,5]);
        $comments = $this->faker->realText();

        $this->restClient->post(
            'satisfaction/public',
            ['score' => $score, 'comments' => $comments]
        )->shouldBeCalled();

        $formData = [
            'comments' => $comments,
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'page' => $this->faker->url,
            'email' => $this->faker->email,
            'score' => $score
        ];

        $event = (new GeneralFeedbackSubmittedEvent())->setFeedbackFormResponse($formData);
        $this->eventDisaptcher->dispatch('general.feedback.submitted', $event)->shouldBeCalled();

        $this->sut->createGeneralFeedback($formData);
    }

    /**
     * @test
     * @dataProvider commentsProvider
     */
    public function createPostSubmissionFeedback(?string $comments, string $expectedCommentsInPostRequest)
    {
        $score = $this->faker->randomElement([1,2,3,4,5]);
        $submittedByUser = UserHelpers::createUser();
        $reportType = $this->faker->randomElement([
            Report::TYPE_COMBINED_HIGH_ASSETS,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
            Report::TYPE_ABBREVIATION_COMBINED,
            Report::TYPE_COMBINED_LOW_ASSETS,
            Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS,
            Report::TYPE_HEALTH_WELFARE
        ]);

        $this->restClient->post(
            'satisfaction',
            ['score' => $score, 'comments' => $expectedCommentsInPostRequest, 'reportType' => $reportType]
        )->shouldBeCalled();

        $feedbackReportObject = (new FeedbackReport())
            ->setComments($comments)
            ->setSatisfactionLevel($score);

        $event = new PostSubmissionFeedbackSubmittedEvent($feedbackReportObject, $submittedByUser);
        $this->eventDisaptcher->dispatch('post.submission.feedback.submitted', $event)->shouldBeCalled();

        $this->sut->createPostSubmissionFeedback($feedbackReportObject, $reportType, $submittedByUser);
    }

    public function commentsProvider()
    {
        return [
            'Comments included' => ['Its greeeeat', 'Its greeeeat'],
            'Empty string comments' => ['', 'Not provided'],
        ];
    }
}
