<?php declare(strict_types=1);


namespace DigidepsTests\Service\Client\Internal;

use AppBundle\Model\Email;
use AppBundle\Service\Client\Internal\SatisfactionApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Faker\Factory;
use Faker\Provider\en_US\Text;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class SatisfactionApiTest extends TestCase
{
    /**
     * @test
     */
    public function create()
    {
        $faker = Factory::create('en_UK');

        /** @var RestClient&ObjectProphecy $restClient */
        $restClient = self::prophesize(RestClient::class);
        $restClient->post(
            'satisfaction/public',
            ['score' => 5, 'comment' => 'Wonderful app made by wonderful people']
        )->shouldBeCalled();

        /** @var MailFactory&ObjectProphecy $mailFactory */
        $mailFactory = self::prophesize(MailFactory::class);

        $formData = [
            'comments' => $faker->realText(),
            'name' => $faker->name,
            'phone' => $faker->phoneNumber,
            'page' => $faker->url,
            'email' => $faker->email,
            'satisfactionLevel' => $faker->randomElement([1,2,3,4,5])
        ];

        $email = new Email();
        $mailFactory->createGeneralFeedbackEmail($formData)->shouldBeCalled()->willReturn($email);

        /** @var MailSender&ObjectProphecy $mailSender */
        $mailSender = self::prophesize(MailSender::class);
        $mailSender->send($email)->shouldBeCalled();

        $sut = new SatisfactionApi();
        $sut->create();
//        $this->getRestClient()->post('satisfaction/public', [
//            'score' => $score,
//            'comments' => $comments,
//        ]);
    }
}
