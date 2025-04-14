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
            !is_array($legacyDecoded) || !is_array($newDecoded)
            || !isset($legacyDecoded['data']) || !is_array($legacyDecoded['data'])
            || !isset($newDecoded['data']) || !is_array($newDecoded['data'])
        ) {
            throw new \RuntimeException('Invalid response format. Expected "data" key with array value.');
        }
        if (
            !isset($legacyDecoded['data']['id']) || !is_numeric($legacyDecoded['data']['id'])
            || !isset($newDecoded['data']['id']) || !is_numeric($newDecoded['data']['id'])
        ) {
            throw new \RuntimeException('Invalid response format. Expected "id" key with numeric value.');
        }

        $legacyId = $legacyDecoded['data']['id'];
        $newId = $newDecoded['data']['id'];

        return $legacyId === $newId;
    }
}
