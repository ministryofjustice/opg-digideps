<?php

namespace App\Service\ResponseComparison;

use Psr\Http\Message\ResponseInterface;

class CourtOrderResponseComparer extends ResponseComparer
{
    private const LEGACY_SECONDARY_URL = "/v2/client/%s?groups%%5B0%%5D=client&groups%%5B1%%5D=client-users&groups%%5B2%%5D=user&groups%%5B3%%5D=client-reports&groups%%5B4%%5D=client-ndr&groups%%5B5%%5D=ndr&groups%%5B6%%5D=report&groups%%5B7%%5D=status&groups%%5B8%%5D=client-deputy&groups%%5B9%%5D=deputy&groups%%5B10%%5D=client-organisations&groups%%5B11%%5D=organisation";
    private const NEW_SECONDARY_URL = "/v2/courtorder/%s";

    public function getSqlStatement(string $userIds): string
    {
        return '
            SELECT d.id as user_id, d.deputy_uid as id1
            FROM dd_user d
            WHERE d.deputy_uid is not null
            AND d.deputy_uid != 0
            AND odr_enabled != true
            AND id in (' . $userIds . ')
        ';
    }

    public function getRoute(): string
    {
        return 'client/get-all-clients-by-deputy-uid/{deputy_uid}';
    }

    private function legacyResponseData(array $legacyDecoded): array
    {
        $legacyNormalized = array_map(function ($row) {
            return [
                'idForNextApiCalls' => $row['id'] ?? null,
                'caseNumber' => $row['case_number'] ?? null,
                'firstName'  => $row['firstname'] ?? null,
                'lastName'   => $row['lastname'] ?? null,
            ];
        }, $legacyDecoded['data']);

        return $legacyNormalized;
    }

    private function newResponseData(array $newDecoded): array
    {
        $newNormalized = array_map(function ($row) {
            return [
                'idForNextApiCalls' => $row['courtOrder']['courtOrderLink'] ?? null,
                'caseNumber'    => $row['client']['caseNumber'] ?? null,
                'firstName'     => $row['client']['firstName'] ?? null,
                'lastName'      => $row['client']['lastName'] ?? null,
            ];
        }, $newDecoded['data']);

        return $newNormalized;
    }

    private function getFormattedExtraData(array $extraLegacyContent, array $extraNewContent): array
    {
        $legacyClientId = $extraLegacyContent['data']['id'];
        $legacyReports = [];
        foreach ($extraLegacyContent['data']['reports'] as $report) {
            $legacyReports[] = [
                'id' => $report['id'],
                'start_date' => $report['start_date'],
                'type' => $report['type']
            ];
        }

        $legacyUsers = [];
        foreach ($extraLegacyContent['data']['users'] as $user) {
            $legacyUsers[] = [
                'deputy_uid' => $user['deputy_uid']
            ];
        }

        $newClientId = $extraNewContent['data']['client']['id'];
        $newReports = [];
        foreach ($extraNewContent['data']['reports'] as $report) {
            $newReports[] = [
                'id' => $report['id'],
                'start_date' => $report['start_date'],
                'type' => $report['type']
            ];
        }

        $newUsers = [];
        foreach ($extraNewContent['data']['active_deputies'] as $user) {
            $newUsers[] = [
                'deputy_uid' => $user['deputy_uid']
            ];
        }

        $extraResultsLegacy[] = [
            'client_id' => $legacyClientId,
            'reports'  => $legacyReports,
            'users'     => $legacyUsers,
        ];

        $extraResultsNew[] = [
            'client_id' => $newClientId,
            'reports'  => $newReports,
            'users'     => $newUsers,
        ];

        $extraResults['new'] = $extraResultsNew;
        $extraResults['legacy'] = $extraResultsLegacy;

        return $extraResults;
    }

    private function sortByIdRecursive(array $data): array
    {
        foreach ($data as &$item) {
            if (is_array($item)) {
                $item = $this->sortByIdRecursive($item);
            }
        }
        unset($item);

        // If this array contains multiple associative sub-arrays with "id" or "client_id"
        if (!empty($data) && is_array(reset($data))) {
            if (array_key_exists('id', reset($data))) {
                usort($data, fn($a, $b) => $a['id'] <=> $b['id']);
            } elseif (array_key_exists('client_id', reset($data))) {
                usort($data, fn($a, $b) => $a['client_id'] <=> $b['client_id']);
            }
        }

        return $data;
    }

    private function normalizeArrayForComparison(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_numeric($value)) {
                $value = (string) $value; // normalize numbers to string
            }
        });

        return $data;
    }

    public function compare(ResponseInterface $legacyResponse, ResponseInterface $newResponse, string $baseUrl, callable $getApiResponse): array
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
        $legacyNormalized = $this->legacyResponseData($legacyDecoded);

        // Normalize new
        $newNormalized = $this->newResponseData($newDecoded);

        // Sort arrays so order doesn’t matter
        $sortFn = function ($a, $b) {
            return [$a['caseNumber'], $a['firstName'], $a['lastName']]
                <=> [$b['caseNumber'], $b['firstName'], $b['lastName']];
        };

        usort($legacyNormalized, $sortFn);
        usort($newNormalized, $sortFn);

        // === Extra fetch step ===

        $legacyNextUriTpl = self::LEGACY_SECONDARY_URL;
        $newNextUriTpl = self::NEW_SECONDARY_URL;

        $legacyNextUrlTpl = rtrim($baseUrl, '/') . '/' . ltrim($legacyNextUriTpl, '/');
        $newNextUrlTpl = rtrim($baseUrl, '/') . '/' . ltrim($newNextUriTpl, '/');

        $extraResults = [];
        foreach ($legacyNormalized as $idx => $legacyRow) {
            $legacyId = $legacyNormalized[$idx]['idForNextApiCalls'] ?? null;
            $newUid   = $newNormalized[$idx]['idForNextApiCalls'] ?? null;

            if ($legacyId && $newUid) {
                $legacyUrl = sprintf($legacyNextUrlTpl, $legacyId);
                $newUrl    = sprintf($newNextUrlTpl, $newUid);

                try {
                    $extraLegacy = $getApiResponse($legacyUrl);
                    $extraNew    = $getApiResponse($newUrl);

                    $extraLegacyContent = json_decode($extraLegacy->getBody()->getContents(), true);
                    $extraNewContent = json_decode($extraNew->getBody()->getContents(), true);

                    if (!is_array($extraLegacyContent)) {
                        $extraLegacyContent = [];
                    }

                    if (!is_array($extraNewContent)) {
                        $extraNewContent = [];
                    }

                    $extraResults = $this->getFormattedExtraData($extraLegacyContent, $extraNewContent);
                } catch (\Throwable $e) {
                    $extraResults['legacy'] = [
                        'deputyId'   => $legacyId,
                        'error'      => $e->getMessage(),
                    ];
                    $extraResults['new'] = [
                        'courtOrder' => $newUid,
                        'error'      => $e->getMessage(),
                    ];
                }
            }
            $legacyNormalized[$idx]['extra'][] = $extraResults['legacy'];
            unset($legacyNormalized[$idx]['idForNextApiCalls']);
            $newNormalized[$idx]['extra'][] = $extraResults['new'];
            unset($newNormalized[$idx]['idForNextApiCalls']);
        }

        $legacyNormalizedSorted = $this->sortByIdRecursive($this->normalizeArrayForComparison($legacyNormalized));
        $newNormalizedSorted = $this->sortByIdRecursive($this->normalizeArrayForComparison($newNormalized));

        return [
            'matching' => $legacyNormalizedSorted === $newNormalizedSorted,
            'legacy'   => json_encode($legacyNormalizedSorted, JSON_PRETTY_PRINT),
            'new'      => json_encode($newNormalizedSorted, JSON_PRETTY_PRINT),
        ];
    }
}
