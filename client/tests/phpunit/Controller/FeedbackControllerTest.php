<?php declare(strict_types=1);


namespace AppBundle\Controller;

use AppBundle\Service\Mailer\NotifyClientMock;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeedbackControllerTest extends WebTestCase
{
    /**
     * @var NotifyClientMock
     */
    private $notifyClientMock;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->Container = $this->client->getContainer();
        $this->notifyClientMock = $this->Container->get('AppBundle\Service\Mailer\NotifyClientMock');
    }
    /**
     * @test
     */
    public function create()
    {
        $crawler = $this->client->request('GET', "/feedback");
        $button = $crawler->selectButton('Send feedback');

        $this->client->submit($button->form(), [
            'feedback[specificPage]' => '1',
            'feedback[comments]' => 'I love it',
            'feedback[name]' => 'Sufjan Stevens',
            'feedback[email]' => 's.stevens@ashmatic-kitty.org',
            'feedback[phone]' => '001555123456',
            'feedback[satisfactionLevel]' => '4',
        ]);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertCount(1, $this->notifyClientMock->getSentEmails());
        ;
    }
}
