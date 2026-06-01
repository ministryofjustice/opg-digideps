<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Helpers;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\User;

class FixtureHelperBuilder
{
    public const string S3_BUCKETNAME = 'S3_BUCKETNAME';

    public static function buildUserDetails(User $user): array
    {
        $client = $user->isLayDeputy() ? $user->getFirstClient() : $user?->getOrganisations()[0]?->getClients()[0];

        if ($client) {
            $currentReport = $client->getCurrentReport();
            $currentReportType = $currentReport?->getType();
            $previousReport = $client->getReports()[0];
        } else {
            $currentReport = null;
            $currentReportType = null;
            $previousReport = null;
        }

        $userDetails = [
            'userId' => $user->getId(),
            'userEmail' => $user->getEmail(),
            'userRole' => $user->getRoleName(),
            'userFirstName' => $user->getFirstname(),
            'userLastName' => $user->getLastname(),
            'userFullName' => $user->getFullName(),
            'userFullAddressArray' => self::buildUserAddressArray($user),
            'userPhone' => $user->getPhoneMain(),
            'clientId' => $client?->getId(),
            'clientFirstName' => $client?->getFirstname(),
            'clientLastName' => $client?->getLastname(),
            'clientFullAddressArray' => $client ? self::buildClientAddressArray($client) : null,
            'clientEmail' => $client?->getEmail(),
            'clientCaseNumber' => $client?->getCaseNumber(),
            'clientArchivedAt' => $client?->getArchivedAt(),
            'currentReportId' => $currentReport?->getId(),
            'currentReportType' => $currentReportType,
            'currentReport' => 'report',
            'currentReportDueDate' => $currentReport?->getDueDate(),
            'currentReportStartDate' => $currentReport?->getStartDate(),
            'currentReportEndDate' => $currentReport?->getEndDate(),
            'currentReportBankAccountId' => $currentReport?->getBankAccounts()[0]?->getId(),
            'courtDate' => $client ? $client->getCourtDate()?->format('j F Y') : null,
        ];

        if ($previousReport && $currentReport && $previousReport->getId() !== $currentReport->getId()) {
            $userDetails = array_merge(
                $userDetails,
                [
                    'previousReportId' => $previousReport->getId(),
                    'previousReportType' => $previousReport->getType(),
                    'previousReport' => 'report',
                    'previousReportDueDate' => $previousReport->getDueDate(),
                    'previousReportStartDate' => $previousReport->getStartDate(),
                    'previousReportEndDate' => $previousReport->getEndDate(),
                    'previousReportBankAccountId' => $previousReport->getBankAccounts()[0]->getId(),
                ]
            );
        }

        return $userDetails;
    }

    public static function buildOrgUserDetails(User $user): array
    {
        $organisation = $user->getOrganisations()->first();
        $deputy = $organisation?->getClients()[0]->getDeputy();

        if ($deputy) {
            $details = [
                'organisationName' => $organisation->getName(),
                'organisationEmailIdentifier' => $organisation->getEmailIdentifier(),
                'deputyName' => sprintf(
                    '%s %s',
                    $deputy->getFirstname(),
                    $deputy->getLastName()
                ),
                'deputyFullAddressArray' => self::buildDeputyAddressArray($deputy),
                'deputyPhone' => $deputy->getPhoneMain(),
                'deputyPhoneAlt' => $deputy->getPhoneAlternative(),
                'deputyEmail' => $deputy->getEmail1(),
                'deputyEmailAlt' => $deputy->getEmail2(),
            ];
        }

        return array_merge(self::buildUserDetails($user), $details);
    }

    public static function buildAdminUserDetails(User $user): array
    {
        return [
            'userId' => $user->getId(),
            'userEmail' => $user->getEmail(),
            'userRole' => $user->getRoleName(),
            'userFirstName' => $user->getFirstname(),
            'userLastName' => $user->getLastname(),
            'userFullName' => $user->getFullName(),
            'userFullAddressArray' => self::buildUserAddressArray($user),
        ];
    }

    public static function buildUserAddressArray(User $user): array
    {
        return array_filter(
            [
                'address1' => $user->getAddress1(),
                'address2' => $user->getAddress2(),
                'address3' => $user->getAddress3(),
                'addressPostcode' => $user->getAddressPostcode(),
                'addressCountry' => $user->getAddressCountry(),
            ],
            function ($value, $key) {
                return !is_null($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function buildClientAddressArray(Client $client): array
    {
        return array_filter(
            [
                'address1' => $client->getAddress(),
                'address2' => $client->getAddress2(),
                'address3' => $client->getAddress3(),
                'addressPostcode' => $client->getPostcode(),
                'addressCountry' => $client->getCountry(),
            ],
            function ($value, $key) {
                return !is_null($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function buildDeputyAddressArray(Deputy $deputy): array
    {
        return array_filter(
            [
                'address1' => $deputy->getAddress1(),
                'address2' => $deputy->getAddress2(),
                'address3' => $deputy->getAddress3(),
                'address4' => $deputy->getAddress4(),
                'address5' => $deputy->getAddress5(),
                'addressPostcode' => $deputy->getAddressPostcode(),
                'addressCountry' => $deputy->getAddressCountry(),
            ],
            function ($value, $key) {
                return !is_null($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
}
