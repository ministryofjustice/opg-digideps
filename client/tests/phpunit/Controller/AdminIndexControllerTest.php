<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Admin\IndexController;
use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSenderInterface;
use AppBundle\Service\OrgService;
use Exception;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class AdminIndexControllerTest extends WebTestCase
{
    /** @var OrgService&ObjectProphecy */
    private $orgService;

    /** @var IndexController */
    private $sut;

    /** @var RouterInterface */
    private static $router;

    public static function setUpBeforeClass(): void
    {
        $client = self::createClient();
        self::$router = $client->getContainer()->get('router');
    }

    public function setUp(): void
    {
        $this->orgService = self::prophesize(OrgService::class);
        $this->sut = new IndexController($this->orgService->reveal());
    }

    public function getRouteMap()
    {
        return [
            ['/admin/', 'indexAction'],
            ['/admin/user-add', 'addUserAction'],
            ['/admin/edit-user', 'editUserAction'],
            ['/admin/send-activation-link/test@email.com', 'sendUserActivationLinkAction', ['email' => 'test@email.com']],
        ];
    }

    /**
     * @dataProvider getRouteMap
     */
    public function testRoutes(string $url, string $action, array $params = []): void
    {
        $match = self::$router->match($url);

        self::assertEquals(get_class($this->sut) . '::' . $action, $match['_controller']);
        foreach ($params as $key => $expectedValue) {
            self::assertEquals($expectedValue, $match[$key]);
        }
    }

    public function testSendActivationLink(): void
    {
        $emailAddress = 'test@gmail.example';

        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSenderInterface::class);
        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $restClient->userRecreateToken($emailAddress, 'pass-reset')->shouldBeCalled()->willReturn(new User());
        $mailFactory->createActivationEmail(new User())->shouldBeCalled()->willReturn(new Email());
        $mailSender->send(new Email(), Argument::cetera())->shouldBeCalled()->willReturn();
        $logger->log(Argument::cetera())->shouldNotBeCalled();

        $response = $this->sut->sendUserActivationLinkAction($emailAddress, $mailFactory->reveal(), $mailSender->reveal(), $logger->reveal(), $restClient->reveal());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('[Link sent]', $response->getContent());
    }

    public function testSendActivationLinkSwallowsFailures(): void
    {
        $emailAddress = 'test@gmail.example';

        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSenderInterface::class);
        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $restClient
            ->userRecreateToken($emailAddress, 'pass-reset')
            ->shouldBeCalled()
            ->willThrow(new Exception('Intentional test exception'));

        $logger->debug('Intentional test exception')->shouldBeCalled();

        $response = $this->sut->sendUserActivationLinkAction($emailAddress, $mailFactory->reveal(), $mailSender->reveal(), $logger->reveal(), $restClient->reveal());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('[Link sent]', $response->getContent());
    }
}
