<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Controller\Report;

use OPG\Digideps\Frontend\Controller\Report\ReportController;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\Status;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Report\ReportSectionService;
use OPG\Digideps\Frontend\Service\Client\Internal\ClientApi;
use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\Client\Internal\SatisfactionApi;
use OPG\Digideps\Frontend\Service\Client\Internal\UserApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\File\Storage\S3Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportControllerTest extends TestCase
{
    public static function deputyTypes(): array
    {
        return [
            'lay deputy' => [true, '/deputyship-details/client/123/edit?reportDeclarationId=456'],
            'organisation deputy' => [false, '/org/client/123/edit?from=declaration'],
        ];
    }

    #[DataProvider('deputyTypes')]
    public function testConfirmDetailsClientEditLink(bool $isLayDeputy, string $expectedUrl): void
    {
        $client = new Client()
            ->setId(123)
            ->setFirstname('Example')
            ->setLastname('Client')
            ->setCurrentReport(new Report()->setId(789));

        $status = $this->createStub(Status::class);
        $status->method('getIsReadyToSubmit')->willReturn(true);

        $report = $this->createStub(Report::class);
        $report->method('getId')->willReturn(456);
        $report->method('getClient')->willReturn($client);
        $report->method('getStatus')->willReturn($status);
        $report->method('isDue')->willReturn(true);

        $reportApi = $this->createMock(ReportApi::class);
        $reportApi->expects(self::once())
            ->method('getReportIfNotSubmitted')
            ->with(456, ReportApi::$reportGroupsAll)
            ->willReturn($report);

        $user = $this->createStub(User::class);
        $user->method('isLayDeputy')->willReturn($isLayDeputy);
        $userApi = $this->createStub(UserApi::class);
        $userApi->method('getUserWithData')->willReturn($user);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $tokenStorage = $this->createStub(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $controller = new ReportController(
            $this->createStub(RestClient::class),
            $reportApi,
            $userApi,
            $this->createStub(ClientApi::class),
            $this->createStub(SatisfactionApi::class),
            $this->createStub(FormFactoryInterface::class),
            $this->createStub(TranslatorInterface::class),
            $this->createStub(S3Storage::class),
            new ReportSectionService(
                $this->createStub(TranslatorInterface::class),
                $this->createStub(RouterInterface::class),
            ),
        );

        $routes = new RouteCollection();
        $routes->add('client_edit', new Route('/deputyship-details/client/{clientId}/edit'));
        $routes->add('org_client_edit', new Route('/org/client/{clientId}/edit'));
        $routes->add('user_edit', new Route('/deputyship-details/your-details/edit'));
        $routes->add('org_profile_edit', new Route('/org/settings/your-details/edit'));

        $container = new Container();
        $container->set('router', new UrlGenerator($routes, new RequestContext()));
        $container->set('security.token_storage', $tokenStorage);
        $controller->setContainer($container);

        $page = $controller->confirmDetailsAction(456);
        self::assertIsArray($page);
        self::assertIsArray($page['contactDetails']);
        self::assertIsArray($page['contactDetails']['client']);
        self::assertSame($expectedUrl, $page['contactDetails']['client']['editUrl']);
    }
}
