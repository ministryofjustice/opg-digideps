<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ndr\Ndr;
use App\Service\Client\Internal\UserApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    public function __construct(
        private readonly UserApi $userApi,
        private readonly string $symfonyEnvironment,
        private readonly bool $fixturesEnabled,
    ) {
    }

    /**
     * @Route("/behat/frontend/user/{email}/details", methods={"GET"}, name="behat_front_get_user_details_by_email")
     */
    public function getUserDetails(string $email): JsonResponse
    {
        if (
            'prod' === $this->symfonyEnvironment
            && !$this->fixturesEnabled
        ) {
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
