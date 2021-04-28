<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ndr\Ndr;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
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

        return new JsonResponse(
            [
                'email' => $user->getEmail(),
                'clientId' => $user->getFirstClient()->getId(),
                'currentReportId' => $currentReport->getId(),
                'currentReportType' => $currentReport->getType(),
                'currentReportNdrOrReport' => $currentReport instanceof Ndr ? 'ndr' : 'report',
                'previousReportId' => $previousReport->getId(),
                'previousReportType' => $previousReport->getType(),
                'previousReportNdrOrReport' => $previousReport instanceof Ndr ? 'ndr' : 'report'
            ]
        );
    }
}
