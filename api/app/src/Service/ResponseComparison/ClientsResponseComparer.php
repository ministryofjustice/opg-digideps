<?php

namespace App\Service\ResponseComparison;

use Psr\Http\Message\ResponseInterface;

class ClientsResponseComparer extends ResponseComparer
{
    public function getSqlStatement(): string
    {
        // SQL we use to decide which user to assume and which ids to use in the URLs
        $sql = '
            SELECT d.id as user_id, c.id as id1
            FROM dd_user d
            INNER JOIN deputy_case dc on dc.user_id = d.id
            INNER JOIN client c on c.id = dc.client_id;
        ';

        return $sql;
    }

    public function getRoute(): string
    {
        return '/client/{client_id}';
    }

    public function compare(ResponseInterface $legacyResponse, ResponseInterface $newResponse): bool
    {
        $legacyBody = json_decode($legacyResponse->getBody()->getContents(), true);
        $newBody = json_decode($newResponse->getBody()->getContents(), true);

        $legacyClients = $legacyBody['data'] ?? [];
        $newClients = $newBody['data'] ?? [];

        $legacyId = $legacyClients['id'];  // First array
        $newId = $newClients['id'];

        return $legacyId === $newId;
    }
}
