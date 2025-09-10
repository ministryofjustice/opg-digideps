<?php

namespace App\Service\ResponseComparison;

use Psr\Http\Message\ResponseInterface;

class CourtOrderResponseComparer extends ResponseComparer
{
    public function getSqlStatement(): string
    {
        return '
            SELECT d.id as user_id, d.deputy_uid as id1
            FROM dd_user d
            WHERE d.deputy_uid is not null
            AND d.deputy_uid != 0
            AND odr_enabled != true;
        ';
    }

    public function getRoute(): string
    {
        return 'client/get-all-clients-by-deputy-uid/{deputy_uid}';
    }

    public function compare(ResponseInterface $legacyResponse, ResponseInterface $newResponse, callable $getApiResponse): array
    {
        $legacyDecoded = json_decode($legacyResponse->getBody()->getContents(), true);
        $newDecoded = json_decode($newResponse->getBody()->getContents(), true);

        if (
            !is_array($legacyDecoded) || !is_array($newDecoded)
            || !isset($legacyDecoded['data']) || !is_array($legacyDecoded['data'])
            || !isset($newDecoded['data']) || !is_array($newDecoded['data'])
        ) {
            throw new \RuntimeException('Invalid response format. Expected "data" key with array value.');
        }

        // Normalize legacy
        $legacyNormalized = array_map(function ($row) {
            return [
                'id'         => $row['id'] ?? null,
                'caseNumber' => $row['case_number'] ?? null,
                'firstName'  => $row['firstname'] ?? null,
                'lastName'   => $row['lastname'] ?? null,
            ];
        }, $legacyDecoded['data']);

        // Normalize new
        $newNormalized = array_map(function ($row) {
            return [
                'courtOrderUid' => $row['courtOrder']['courtOrderUid'] ?? null,
                'caseNumber'    => $row['client']['caseNumber'] ?? null,
                'firstName'     => $row['client']['firstName'] ?? null,
                'lastName'      => $row['client']['lastName'] ?? null,
            ];
        }, $newDecoded['data']);

        // Sort arrays so order doesn’t matter
        $sortFn = function ($a, $b) {
            return [$a['caseNumber'], $a['firstName'], $a['lastName']]
                <=> [$b['caseNumber'], $b['firstName'], $b['lastName']];
        };

        usort($legacyNormalized, $sortFn);
        usort($newNormalized, $sortFn);

        // === Extra fetch step ===
        $legacyNextUrlTpl = "/v2/client/%s?groups%%5B0%%5D=client&groups%%5B1%%5D=client-users&groups%%5B2%%5D=user&groups%%5B3%%5D=client-reports&groups%%5B4%%5D=client-ndr&groups%%5B5%%5D=ndr&groups%%5B6%%5D=report&groups%%5B7%%5D=status&groups%%5B8%%5D=client-deputy&groups%%5B9%%5D=deputy&groups%%5B10%%5D=client-organisations&groups%%5B11%%5D=organisation";
        $newNextUrlTpl = "/v2/courtorder/%s";

        $extraResults = [];
        foreach ($legacyNormalized as $idx => $legacyRow) {
            $legacyId = $legacyRow['id'] ?? null;
            $newUid   = $newNormalized[$idx]['courtOrderUid'] ?? null;

            if ($legacyId && $newUid) {
                $legacyUrl = sprintf($legacyNextUrlTpl, $legacyId);
                $newUrl    = sprintf($newNextUrlTpl, $newUid);

                try {
                    $extraLegacy = $getApiResponse($legacyUrl);
                    $extraNew    = $getApiResponse($newUrl);

                    $extraResults[] = [
                        'legacyId'   => $legacyId,
                        'courtOrder' => $newUid,
                        'legacyApi'  => json_decode($extraLegacy->getBody()->getContents(), true),
                        'newApi'     => json_decode($extraNew->getBody()->getContents(), true),
                    ];
                } catch (\Throwable $e) {
                    $extraResults[] = [
                        'legacyId'   => $legacyId,
                        'courtOrder' => $newUid,
                        'error'      => $e->getMessage(),
                    ];
                }
            }
        }

        return [
            'matching' => $legacyNormalized === $newNormalized,
            'legacy'   => json_encode($legacyNormalized, JSON_PRETTY_PRINT),
            'new'      => json_encode($newNormalized, JSON_PRETTY_PRINT),
            'extra'    => json_encode($extraResults, JSON_PRETTY_PRINT),
        ];
    }
}
