<?php declare(strict_types=1);


namespace DigidepsTests\Service\Client\Internal;

use AppBundle\Event\GeneralFeedbackSubmittedEvent;
use AppBundle\Model\Email;
use AppBundle\Service\Client\Internal\SatisfactionApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Faker\Factory;
use Faker\Provider\en_US\Text;
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
    public function create()
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
            'satisfactionLevel' => $score
        ];

        $event = (new GeneralFeedbackSubmittedEvent())->setFeedbackFormResponse($formData);
        $this->eventDisaptcher->dispatch('general.feedback.submitted', $event)->shouldBeCalled();

        $this->sut->create($formData);
    }
}
