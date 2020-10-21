<?php declare(strict_types=1);


namespace AppBundle\Controller;

use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\NotifyClientMock;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeedbackControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $symfonyClient;

    /**
     * @var NotifyClientMock
     */
    private $notifyClient;

    public function setUp(): void
    {
        $this->symfonyClient = static::createClient(['environment' => 'unit_test', 'debug' => false]);
        $this->symfonyClient->disableReboot();
        $this->notifyClient = $this->symfonyClient->getContainer()->get('Alphagov\Notifications\Client');
    }
    /**
     * @test
     */
    public function create()
    {
        $crawler = $this->symfonyClient->request('GET', "/feedback");
        $button = $crawler->selectButton('Send feedback');

        $comment = 'I love it';
        $name = 'Sufjan Stevens';
        $email = 's.stevens@ashmatic-kitty.org';
        $phone = '001555123456';
        $satisfactionLevel = '4';

        $formValues = [
            'feedback[specificPage]' => '1',
            'feedback[comments]' => $comment,
            'feedback[name]' => $name,
            'feedback[email]' => $email,
            'feedback[phone]' => $phone,
            'feedback[satisfactionLevel]' => $satisfactionLevel,
        ];

        $this->symfonyClient->submit($button->form(), $formValues);


        self::assertEquals(302, $this->symfonyClient->getResponse()->getStatusCode());
        $this->assertEmailIsSent(
            MailFactory::GENERAL_FEEDBACK_TEMPLATE_ID,
            [$comment, $name, $email, $phone, $satisfactionLevel],
            $this->notifyClient->getSentEmails()
        );
    }

    private function assertEmailIsSent(string $emailTemplateId, array $personalisationsToCheck, array $sentEmails)
    {
        self::assertCount(1, $sentEmails);
        self::assertEquals($emailTemplateId, array_key_first($sentEmails));
        foreach ($personalisationsToCheck as $personalisation) {
            self::assertContains($personalisation, array_values($sentEmails[MailFactory::GENERAL_FEEDBACK_TEMPLATE_ID]));
        }
    }
}
