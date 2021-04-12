<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ndr\Ndr;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\TestHelpers\BehatFixtures;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    private UserApi $userApi;
    private string $symfonyEnvironment;
    private RestClient $restClient;

    public function __construct(
        UserApi $userApi,
        string $symfonyEnvironment,
        RestClient $restClient
    ) {
        $this->userApi = $userApi;
        $this->symfonyEnvironment = $symfonyEnvironment;
        $this->restClient = $restClient;
    }

    /**
     * @Route("/behat/frontend/user/{email}/details", name="behat_front_get_user_details_by_email", methods={"GET"})
     *
     * @param string $email
     * @return JsonResponse
     */
    public function getUserDetails(string $email)
    {
        if ($this->symfonyEnvironment === 'prod') {
            throw $this->createNotFoundException();
        }

        $user = $this->userApi->getByEmail(
            $email,
            ['user-login', 'user-id', 'user-email', 'user-clients', 'client', 'current-report', 'client-reports', 'report']
        );

        $currentReport = $user->getFirstClient()->getCurrentReport();
        $previousReport = $user->getFirstClient()->getReports()[0];
        $isLay = $user->getRoleName() === User::ROLE_LAY;
        $client = $isLay ? $user->getFirstClient() : $user->getOrganisations()[0]->getClients()[0];

        return new JsonResponse(
            [
                'email' => $user->getEmail(),
                'userRole' => $user->getRoleName(),
                'clientId' => $client->getId(),
                'clientFirstName' =>  $client->getFirstname(),
                'clientLastName' => $client->getLastname(),
                'clientCaseNumber' => $client->getCaseNumber(),
                'currentReportId' => $currentReport->getId(),
                'currentReportType' => $currentReport->getType(),
                'currentReportNdrOrReport' => $currentReport instanceof Ndr ? 'ndr' : 'report',
                'previousReportId' => $previousReport->getId(),
                'previousReportType' => $previousReport->getType(),
                'previousReportNdrOrReport' => $previousReport instanceof Ndr ? 'ndr' : 'report'
            ]
        );
    }

    /**
     * @Route("/behat/frontend/reset-fixtures", name="behat_front_reset-fixtures", methods={"GET"})
     *
     * @param string $email
     * @return JsonResponse
     */
    public function resetFixtures(Request $request)
    {
        try {
            if ($this->symfonyEnvironment === 'prod') {
                throw $this->createNotFoundException();
            }

            $testRunId = $request->query->get('testRunId');

            $response = $this->restClient->get(
                sprintf('/v2/fixture/reset-fixtures?testRunId=%s', $testRunId),
                'response'
            );

            $users = json_decode($response->getBody()->getContents(), true)['data'];

            return new JsonResponse(
                [
                    'response' => 'Behat fixtures loaded', 'data' => $users
                ]
            );
        } catch (\Throwable $e) {
            return new JsonResponse(
                [
                    'response' => sprintf('Behat fixtures not loaded: %s', $e->getMessage()),
                    'data' => null
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
