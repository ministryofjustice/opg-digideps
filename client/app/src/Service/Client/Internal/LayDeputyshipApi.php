<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Client\Internal;

use OPG\Digideps\Frontend\Service\Client\RestClient;

class LayDeputyshipApi
{
    public const string UPLOAD_LAY_DEPUTYSHIP_ENDPOINT = 'v2/lay-deputyship/upload';

    public function __construct(private RestClient $restClient)
    {
    }

    public function uploadLayDeputyShip(mixed $compressedData, string $chunkId)
    {
        return $this->restClient->setTimeout(600)->post(
            self::UPLOAD_LAY_DEPUTYSHIP_ENDPOINT,
            $compressedData,
            [],
            'array',
            ['headers' => ['chunkId' => $chunkId]]
        );
    }
}
