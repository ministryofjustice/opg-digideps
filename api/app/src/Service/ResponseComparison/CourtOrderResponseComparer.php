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

    public function compare(ResponseInterface $legacyResponse, ResponseInterface $newResponse): array
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
                'caseNumber' => $row['case_number'] ?? null,
                'firstName' => $row['firstname'] ?? null,
                'lastName' => $row['lastname'] ?? null,
            ];
        }, $legacyDecoded['data']);

        // Normalize new
        $newNormalized = array_map(function ($row) {
            return [
                'caseNumber' => $row['client']['caseNumber'] ?? null,
                'firstName' => $row['client']['firstName'] ?? null,
                'lastName' => $row['client']['lastName'] ?? null,
            ];
        }, $newDecoded['data']);

        // Sort arrays so order doesn’t matter
        $sortFn = function ($a, $b) {
            return [$a['caseNumber'], $a['firstName'], $a['lastName']]
                <=> [$b['caseNumber'], $b['firstName'], $b['lastName']];
        };

        usort($legacyNormalized, $sortFn);
        usort($newNormalized, $sortFn);

        return [
            'matching' => $legacyNormalized === $newNormalized,
            'legacy' => json_encode($legacyNormalized, JSON_PRETTY_PRINT),
            'new' => json_encode($newNormalized, JSON_PRETTY_PRINT),
        ];
    }
}
