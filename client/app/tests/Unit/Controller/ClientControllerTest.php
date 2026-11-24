<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Controller;

use OPG\Digideps\Frontend\Controller\ClientController;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\EventDispatcher\ObservableEventDispatcher;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Client\Internal\ClientApi;
use OPG\Digideps\Frontend\Service\Client\Internal\DeputyApi;
use OPG\Digideps\Frontend\Service\Client\Internal\PreRegistrationApi;
use OPG\Digideps\Frontend\Service\Client\Internal\UserApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Validator\Validation;

class ClientControllerTest extends TestCase
{
    private const int CLIENT_ID = 123;

    private ClientApi&MockObject $clientApi;
    private RequestStack $requestStack;
    private ClientController $controller;

    protected function setUp(): void
    {
        $this->clientApi = $this->createMock(ClientApi::class);
        $this->requestStack = new RequestStack();
        $this->controller = new ClientController(
            $this->createStub(UserApi::class),
            $this->clientApi,
            $this->createStub(DeputyApi::class),
            $this->createStub(RestClient::class),
            $this->createStub(PreRegistrationApi::class),
            $this->createStub(ObservableEventDispatcher::class),
        );

        $routes = new RouteCollection();
        $routes->add('client_edit', new Route('/deputyship-details/client/{clientId}/edit'));
        $routes->add('report_confirm_details', new Route('/report/{reportId}/confirm-details'));
        $routes->add('deputyship_details_clients', new Route('/deputyship-details/clients'));

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();

        $container = new Container();
        $container->set('router', new UrlGenerator($routes, new RequestContext()));
        $container->set('form.factory', $formFactory);
        $container->set('request_stack', $this->requestStack);
        $this->controller->setContainer($container);
    }

    public static function currentReportIds(): array
    {
        return [
            'no current report' => [null, 456, '/report/456/confirm-details'],
            'a different current report' => [789, 456, '/report/456/confirm-details'],
            'negative report ID' => [null, -1, '/deputyship-details/clients'],
        ];
    }

    #[DataProvider('currentReportIds')]
    public function testReportIdSurvivesEditingAndValidation(?int $currentReportId, int $reportId, string $expectedRedirect): void
    {
        $client = $this->client();
        if ($currentReportId !== null) {
            $client->setCurrentReport((new Report())->setId($currentReportId));
        }

        $this->clientApi->expects(self::exactly(3))->method('getById')->with(self::CLIENT_ID)->willReturn($client);
        $this->clientApi->expects(self::once())
            ->method('update')
            ->with(
                self::identicalTo($client),
                self::callback(function (Client $updated) use ($client): bool {
                    self::assertNotSame($client, $updated);
                    self::assertSame(self::CLIENT_ID, $updated->getId());
                    self::assertSame('Updated', $updated->getFirstname());
                    return true;
                }),
                AuditEvents::TRIGGER_DEPUTY_USER_EDIT_SELF,
            );

        $page = $this->edit(Request::create('/deputyship-details/client/123/edit?' . http_build_query(['reportDeclarationId' => $reportId])));
        self::assertIsArray($page);
        self::assertInstanceOf(FormView::class, $page['form']);
        $formVars = $page['form']->vars;
        self::assertIsArray($formVars);
        $action = $formVars['action'];
        self::assertIsString($action);
        self::assertSame("/deputyship-details/client/123/edit?action=edit&reportDeclarationId=$reportId", $action);

        $invalid = $this->edit(Request::create($action, 'POST', ['client' => $this->formData('')]));
        self::assertIsArray($invalid);
        self::assertInstanceOf(FormView::class, $invalid['form']);
        $invalidFormVars = $invalid['form']->vars;
        self::assertIsArray($invalidFormVars);
        self::assertFalse($invalidFormVars['valid']);
        $invalidAction = $invalidFormVars['action'];
        self::assertIsString($invalidAction);
        self::assertSame($action, $invalidAction);

        $response = $this->edit(Request::create($invalidAction, 'POST', ['client' => $this->formData('Updated')]));
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame($expectedRedirect, $response->getTargetUrl());
    }

    public static function reportIdsTreatedAsAbsent(): array
    {
        return [
            'missing' => [null],
            'empty' => [''],
            'zero' => ['0'],
            'not an integer' => ['abc'],
            'overflow' => ['999999999999999999999999999999999'],
        ];
    }

    #[DataProvider('reportIdsTreatedAsAbsent')]
    public function testEditingWithoutReportIdReturnsToClientList(?string $reportId): void
    {
        $client = $this->client();
        $this->clientApi->expects(self::exactly(2))->method('getById')->with(self::CLIENT_ID)->willReturn($client);
        $this->clientApi->expects(self::once())->method('update');

        $url = '/deputyship-details/client/123/edit';
        if ($reportId !== null) {
            $url .= '?' . http_build_query(['reportDeclarationId' => $reportId]);
        }
        $page = $this->edit(Request::create($url));
        self::assertIsArray($page);
        self::assertInstanceOf(FormView::class, $page['form']);
        $formVars = $page['form']->vars;
        self::assertIsArray($formVars);
        $action = $formVars['action'];
        self::assertIsString($action);
        self::assertSame('/deputyship-details/client/123/edit?action=edit', $action);

        $response = $this->edit(Request::create($action, 'POST', ['client' => $this->formData('Updated')]));
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/deputyship-details/clients', $response->getTargetUrl());
    }

    public function testArrayReportIdIsRejected(): void
    {
        $this->clientApi->expects(self::never())->method('getById');
        $this->expectException(BadRequestException::class);

        $this->edit(Request::create('/deputyship-details/client/123/edit?reportDeclarationId[]=456'));
    }

    private function edit(Request $request): array|RedirectResponse
    {
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->requestStack->push($request);

        try {
            return $this->controller->editClientDetailsAction($request, self::CLIENT_ID);
        } finally {
            $this->requestStack->pop();
        }
    }

    private function client(): Client
    {
        return new Client()
            ->setId(self::CLIENT_ID)
            ->setFirstname('Original')
            ->setLastname('Client')
            ->setCaseNumber('12345678')
            ->setAddress('1 Example Street')
            ->setPostcode('SW1A 1AA')
            ->setCountry('GB');
    }

    private function formData(string $firstname): array
    {
        return [
            'firstname' => $firstname,
            'lastname' => 'Client',
            'caseNumber' => '12345678',
            'address' => '1 Example Street',
            'address2' => '',
            'address3' => '',
            'address4' => '',
            'address5' => '',
            'postcode' => 'SW1A 1AA',
            'country' => 'GB',
            'phone' => '',
            'id' => '999',
            'save' => '',
        ];
    }
}
