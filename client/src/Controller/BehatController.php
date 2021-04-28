<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ndr\Ndr;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @return JsonResponse
     */
    public function getUserDetails(string $email)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $user = $this->userApi->getByEmail(
            $email,
            ['user-login', 'user-id', 'user-email', 'user-clients', 'user-rolename', 'client', 'current-report', 'client-reports', 'report', 'ndr']
        );

        $client = $user->isLayDeputy() ? $user->getFirstClient() : $user->getOrganisations()[0]->getClients()[0];
        $currentReport = $user->isNdrEnabled() ? $client->getNdr() : $client->getCurrentReport();
        $currentReportType = $user->isNdrEnabled() ? null : $currentReport->getType();
        $previousReport = $user->isNdrEnabled() ? null : $client->getReports()[0];

        $userDetails = [
            'userEmail' => $user->getEmail(),
            'userRole' => $user->getRoleName(),
            'userFirstName' => $user->getFirstname(),
            'userLastName' => $user->getLastname(),
            'userFullName' => $user->getFullName(),
            'userFullAddressArray' => array_filter([
                $user->getAddress1(),
                $user->getAddress2(),
                $user->getAddress3(),
                $user->getAddressPostcode(),
                $user->getAddressCountry(),
            ]),
            'userPhone' => $user->getPhoneMain(),
            'courtOrderNumber' => $client->getCaseNumber(),
            'clientId' => $client->getId(),
            'clientFirstName' => $client->getFirstname(),
            'clientLastName' => $client->getLastname(),
            'clientCaseNumber' => $client->getCaseNumber(),
            'currentReportId' => $currentReport->getId(),
            'currentReportType' => $currentReportType,
            'currentReportNdrOrReport' => $currentReport instanceof Ndr ? 'ndr' : 'report',
            'currentReportDueDate' => $currentReport->getDueDate()->format('j F Y'),
        ];

        if ($previousReport) {
            $userDetails = array_merge(
                $userDetails,
                [
                    'previousReportId' => $previousReport->getId(),
                    'previousReportType' => $previousReport->getType(),
                    'previousReportNdrOrReport' => $previousReport instanceof Ndr ? 'ndr' : 'report',
                    'previousReportDueDate' => $previousReport->getDueDate()->format('j F Y'),
                ]
            );
        }

        return new JsonResponse($userDetails);
    }
}
