<?php

namespace App\Service\ResponseComparison;

use Psr\Http\Message\ResponseInterface;

class ClientsResponseComparer extends ResponseComparer
{
    public function getSqlStatement(): string
    {
        return '
            SELECT d.id as user_id, c.id as id1
            FROM dd_user d
            INNER JOIN deputy_case dc on dc.user_id = d.id
            INNER JOIN client c on c.id = dc.client_id;
        ';
    }

    public function getRoute(): string
    {
        return '/client/{client_id}';
    }

    public function compare(ResponseInterface $legacyResponse, ResponseInterface $newResponse): bool
    {
        $legacyDecoded = json_decode($legacyResponse->getBody()->getContents(), true);
        $newDecoded = json_decode($newResponse->getBody()->getContents(), true);

        if (
            !is_array($legacyDecoded) || !isset($legacyDecoded['data']) || !is_array($legacyDecoded['data'])
            || !is_array($newDecoded) || !isset($newDecoded['data']) || !is_array($newDecoded['data'])
        ) {
            throw new \RuntimeException('Invalid response format. Expected "data" key with array value.');
        }

        $legacyId = $legacyDecoded['data']['id'] ?? null;
        $newId = $newDecoded['data']['id'] ?? null;

        if (!is_scalar($legacyId) || !is_scalar($newId)) {
            throw new \RuntimeException('Invalid "id" format in data.');
        }

        return $legacyId === $newId;
    }
}
