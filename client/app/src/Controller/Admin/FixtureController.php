<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Form\Admin\Fixture\LayCourtOrderFixtureType;
use App\Form\Admin\Fixture\OrgCourtOrderFixtureType;
use App\Form\Admin\Fixture\PreRegistrationFixtureType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\TestHelpers\ClientHelpers;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/admin/fixtures')]
class FixtureController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly UserApi $userApi,
        private readonly bool $fixturesEnabled
    ) {
    }

    #[Route(path: '/', name: 'admin_fixtures')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Fixtures/index.html.twig')]
    public function fixtures(): array
    {
        return [];
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/court-orders/lay', name: 'admin_lay_fixtures_court_orders')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Fixtures/layCourtOrders.html.twig')]
    public function layCourtOrdersAction(Request $request): array
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(LayCourtOrderFixtureType::class, null, [
            'deputyType' => $request->get('deputy-type', User::TYPE_LAY),
            'reportType' => $request->get('report-type', Report::TYPE_HEALTH_WELFARE),
            'reportStatus' => $request->get('report-status', Report::STATUS_NOT_STARTED),
            'multiClientEnabled' => $request->get('multi-client-enabled', false),
            'coDeputyEnabled' => $request->get('co-deputy-enabled', false),
            'activated' => $request->get('activated', true),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formAndRequestData = $this->retrieveFormData($form, $request);
            $submittedFormData = $formAndRequestData['submitted'];

            $response = $this->restClient->post('v2/fixture/court-order', json_encode([
                'deputyType' => $submittedFormData['deputyType'],
                'deputyEmail' => $formAndRequestData['deputyEmail'],
                'caseNumber' => $formAndRequestData['caseNumber'],
                'reportType' => $submittedFormData['reportType'],
                'reportStatus' => $submittedFormData['reportStatus'],
                'courtDate' => $formAndRequestData['courtDate']->format('Y-m-d'),
                'multiClientEnabled' => $submittedFormData['multiClientEnabled'],
                'coDeputyEnabled' => $submittedFormData['coDeputyEnabled'],
                'activated' => $submittedFormData['activated'],
                'deputyUid' => $formAndRequestData['deputyUid'],
            ]));

            $query = ['query' => ['filter_by_ids' => implode(',', $response['deputyIds'])]];

            $deputiesData = $this->restClient->get('/user/get-all', 'array', [], $query);
            $sanitizedDeputyData = $this->removeNullValues($deputiesData);

            $deputies = $this->serializer->deserialize(json_encode($sanitizedDeputyData), 'App\Entity\User[]', 'json');
            $caseNumber = $response['multiClientCaseNumbers'] ?? [$formAndRequestData['caseNumber']];

            $deputyEmails = [];
            foreach (array_reverse($deputies) as $deputy) {
                $deputyEmails[] = $deputy->getEmail();
            }

            // More deputy emails than case numbers implies co-deputy user and therefore needs matching to primary case number
            $deputyAndCaseNumber = [];
            if (count($caseNumber) > 1) {
                if (count($deputyEmails) > count($caseNumber)) {
                    $deputyAndCaseNumber[] = [
                        $deputyEmails[0] => $caseNumber[0],
                        $deputyEmails[1] => $caseNumber[0],
                        $deputyEmails[2] => $caseNumber[1],
                    ];
                } else {
                    for ($i = 0; $i < min(count($deputyEmails), count($caseNumber)); ++$i) {
                        $deputyAndCaseNumber[] = [
                            $deputyEmails[$i] => $caseNumber[$i],
                        ];
                    }
                }
            }

            if ($submittedFormData['multiClientEnabled']) {
                $this->addFlash('courtOrderFixture', ['deputyAndCaseNumber' => $deputyAndCaseNumber, 'caseNumber' => $caseNumber]);
            } else {
                $this->addFlash('courtOrderFixture', ['deputies' => array_reverse($deputies), 'caseNumber' => $caseNumber]);
            }
        }

        return ['form' => $form->createView()];
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/court-orders/org', name: 'admin_org_fixtures_court_orders')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Fixtures/orgCourtOrders.html.twig')]
    public function orgCourtOrdersAction(Request $request): array
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(OrgCourtOrderFixtureType::class, null, [
            'deputyType' => $request->get('deputy-type', User::TYPE_PROF),
            'reportType' => $request->get('report-type', Report::TYPE_HEALTH_WELFARE),
            'reportStatus' => $request->get('report-status', Report::STATUS_NOT_STARTED),
            'activated' => $request->get('activated', true),
            'orgSizeClients' => $request->get('orgSizeClients', 1),
            'orgSizeUsers' => $request->get('orgSizeUsers', 1),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formAndRequestData = $this->retrieveFormData($form, $request);
            $submittedFormData = $formAndRequestData['submitted'];

            $response = $this->restClient->post('v2/fixture/court-order', json_encode([
                'deputyType' => $submittedFormData['deputyType'],
                'deputyEmail' => $formAndRequestData['deputyEmail'],
                'caseNumber' => $formAndRequestData['caseNumber'],
                'reportType' => $submittedFormData['reportType'],
                'reportStatus' => $submittedFormData['reportStatus'],
                'courtDate' => $formAndRequestData['courtDate']->format('Y-m-d'),
                'activated' => $submittedFormData['activated'],
                'orgSizeClients' => $submittedFormData['orgSizeClients'],
                'orgSizeUsers' => $submittedFormData['orgSizeUsers'],
                'deputyUid' => $formAndRequestData['deputyUid'],
            ]));

            $query = ['query' => ['filter_by_ids' => implode(',', $response['deputyIds'])]];
            $deputiesData = $this->restClient->get('/user/get-all', 'array', [], $query);
            $sanitizedDeputyData = $this->removeNullValues($deputiesData);

            $deputies = $this->serializer->deserialize(json_encode($sanitizedDeputyData), 'App\Entity\User[]', 'json');

            $this->addFlash('courtOrderFixture', ['deputies' => array_reverse($deputies), 'caseNumber' => [$formAndRequestData['caseNumber']]]);
        }

        return ['form' => $form->createView()];
    }

    /**
     * @throws \Exception
     */
    private function retrieveFormData(FormInterface $form, Request $request): array
    {
        $submitted = $form->getData();

        /** @var ?string $courtDate */
        $courtDate = $request->get('court-date');
        $courtDate = is_null($courtDate) ? new \DateTime() : new \DateTime($courtDate);

        /** @var ?string $deputyType */
        $deputyType = $submitted['deputyType'];
        if (!is_null($deputyType)) {
            $deputyType = strtolower($deputyType);
        };

        $deputyEmail = $request->query->get('deputy-email', sprintf('original-%s-deputy-%s@fixture.com', $deputyType, mt_rand(1000, 9999)));
        $caseNumber = $request->get('case-number', ClientHelpers::createValidCaseNumber());
        $deputyUid = intval('7' . str_pad((string) mt_rand(1, 99999999), 11, '0', STR_PAD_LEFT));

        return [
            'submitted' => $submitted,
            'courtDate' => $courtDate,
            'deputyEmail' => $deputyEmail,
            'caseNumber' => $caseNumber,
            'deputyUid' => $deputyUid,
        ];
    }

    /**
     * @TODO replace with https://symfony.com/doc/4.4/components/serializer.html#skipping-null-values
     * when using Symfony 4+
     */
    private function removeNullValues(array $deputiesDataArray): array
    {
        foreach ($deputiesDataArray as $index => $properties) {
            foreach ($properties as $key => $property) {
                if (is_null($property)) {
                    unset($properties[$key]);
                }
            }

            $deputiesDataArray[$index] = $properties;
        }

        return $deputiesDataArray;
    }

    #[Route(path: '/complete-sections/{reportType}/{reportId}', name: 'fixtures_complete_report_sections', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    public function completeReportSectionsAction(Request $request, string $reportType, int $reportId): JsonResponse
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $sections = $request->get('sections');

        $this
            ->restClient
            ->put("v2/fixture/complete-sections/$reportType/$reportId?sections=$sections", []);

        return new JsonResponse(['Report updated']);
    }

    #[Route(path: '/createAdmin', name: 'fixtures_create_admin', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    public function createAdmin(Request $request): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $this
            ->restClient
            ->post('v2/fixture/createAdmin', json_encode([
                'adminType' => $request->query->get('adminType'),
                'email' => $request->query->get('email'),
                'firstName' => $request->query->get('firstName'),
                'lastName' => $request->query->get('lastName'),
                'activated' => $request->query->get('activated'),
            ]));

        return new Response();
    }

    #[Route(path: '/getUserIDByEmail/{email}', name: 'fixtures_get_user_id_by_email', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    public function getUserIDByEmail(string $email): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        /** @var array $response */
        $response = json_decode((string) $this
            ->restClient
            ->get("v2/fixture/getUserIDByEmail/$email", 'response')->getBody(), true);

        if ($response['success']) {
            return new Response($response['data']['id']);
        } else {
            return new Response($response['message'], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route(path: '/createUser', name: 'fixtures_create_user', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN', 'ROLE_AD')"))]
    public function createUser(Request $request): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $this
            ->restClient
            ->post('v2/fixture/createUser', json_encode([
                'ndr' => $request->query->get('ndr'),
                'deputyType' => $request->query->get('deputyType'),
                'deputyEmail' => $request->query->get('email'),
                'firstName' => $request->query->get('firstName'),
                'lastName' => $request->query->get('lastName'),
                'postCode' => $request->query->get('postCode'),
                'activated' => $request->query->get('activated'),
            ]));

        return new Response();
    }

    #[Route(path: '/deleteUser', name: 'fixtures_delete_user', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function deleteUser(Request $request): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $this
            ->restClient
            ->post('v2/fixture/deleteUser', json_encode([
                'email' => $request->query->get('email'),
            ]));

        return new Response();
    }

    /**
     * @throws \Throwable
     */
    #[Route(path: '/createClientAttachDeputy', name: 'fixtures_create_client_attach_deputy', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN', 'ROLE_AD')"))]
    public function createClientAndAttachToDeputy(Request $request): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $this
            ->restClient
            ->post(
                'v2/fixture/createClientAttachDeputy',
                json_encode(
                    [
                        'firstName' => $request->query->get('firstName'),
                        'lastName' => $request->query->get('lastName'),
                        'phone' => $request->query->get('phone'),
                        'address' => $request->query->get('address'),
                        'address2' => $request->query->get('address2'),
                        'county' => $request->query->get('county'),
                        'postCode' => $request->query->get('postCode'),
                        'caseNumber' => $request->query->get('caseNumber'),
                        'deputyEmail' => $request->query->get('deputyEmail'),
                    ]
                )
            );

        return new Response();
    }

    /**
     * @throws \Throwable
     */
    #[Route(path: '/createClientAttachOrgs', name: 'fixtures_create_client_attach_org', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN', 'ROLE_AD')"))]
    public function createClientAndAttachToOrg(Request $request): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $this
            ->restClient
            ->post(
                'v2/fixture/createClientAttachOrgs',
                json_encode(
                    [
                        'firstName' => $request->query->get('firstName'),
                        'lastName' => $request->query->get('lastName'),
                        'phone' => $request->query->get('phone'),
                        'address' => $request->query->get('address'),
                        'address2' => $request->query->get('address2'),
                        'county' => $request->query->get('county'),
                        'postCode' => $request->query->get('postCode'),
                        'caseNumber' => $request->query->get('caseNumber'),
                        'orgEmailIdentifier' => $request->query->get('orgEmailIdentifier'),
                        'deputyEmail' => $request->query->get('deputyEmail'),
                    ]
                )
            );

        return new Response();
    }

    #[Route(path: '/user-registration-token', name: 'fixtures_get_user_registration_token', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN', 'ROLE_AD')"))]
    public function getUserRegistrationToken(Request $request): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $email = $request->query->get('email');
        $user = $this->userApi->getByEmail($email);

        return new Response($user->getRegistrationToken());
    }

    #[Route(path: '/create-pre-registration', name: 'pre_registration_fixture', methods: ['GET', 'POST'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Fixtures/preRegistration.html.twig')]
    public function createPreRegistration(Request $request): array
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(PreRegistrationFixtureType::class, null, [
            'deputyType' => $request->get('deputy-type', User::TYPE_LAY),
            'multiClientEnabled' => $request->get('multiClientEnabled', false),
            'reportType' => $request->get('report-type', 'OPG102'),
            'createCoDeputy' => $request->get('create-co-deputy', false),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array $submitted */
            $submitted = $form->getData();

            $response = $this->restClient->post('v2/fixture/create-pre-registration', json_encode([
                'deputyType' => $submitted['deputyType'],
                'multiClientEnabled' => $submitted['multiClientEnabled'],
                'reportType' => $submitted['reportType'],
                'createCoDeputy' => $submitted['createCoDeputy'],
            ]));

            $this->addFlash('preRegFixture', $response);
        }

        return ['form' => $form->createView()];
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/unsubmit-report/{reportId}', name: 'unsubmit_report_fixture', methods: ['GET', 'POST'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function unsubmitReport(int $reportId): void
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        try {
            $report = $this->reportApi->getReport($reportId);

            /** @var User $user */
            $user = $this->getUser();

            $this->reportApi->unsubmit($report, $user, 'Fixture tests');
        } catch (\Throwable $e) {
            throw new \Exception(sprintf('Could not unsubmit report %s: %s', $reportId, $e->getMessage()));
        }
    }

    #[Route(path: '/move-users-clients-to-users-org/{userEmail}', name: 'move_users_clients_to_org', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function moveUsersClientsToUsersOrg(string $userEmail): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        try {
            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $this
                ->restClient
                ->get("v2/fixture/move-users-clients-to-users-org/$userEmail", 'response');

            if ($response->getStatusCode() > 399) {
                return new Response(sprintf('Could not move %s clients to users org: %s', $userEmail, $response->getBody()->getContents()), 500);
            }

            return new Response('Clients added to Users org');
        } catch (\Throwable $e) {
            return new Response(sprintf('Could not move %s clients to users org: %s', $userEmail, $e->getMessage()), 500);
        }
    }

    #[Route(path: '/activateOrg/{orgName}', name: 'activate_org', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function activateOrg(string $orgName): Response
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $response = new Response();

        try {
            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $this
                ->restClient
                ->get("v2/fixture/activateOrg/$orgName", 'response');

            if ($response->getStatusCode() > 399) {
                return new Response(sprintf('Could not activate %s org: %s', $orgName, $response->getBody()->getContents()), 500);
            }

            return new Response('Org activated');
        } catch (\Throwable) {
            return new Response(sprintf('Could not activate %s org: %s', $orgName, $response->getBody()->getContents()), 500);
        }
    }
}
