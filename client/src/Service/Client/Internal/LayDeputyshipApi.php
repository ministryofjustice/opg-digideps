<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class LayDeputyshipApi
{
    public const UPLOAD_LAY_DEPUTYSHIP_ENDPOINT = 'v2/lay-deputyship/upload';

    public function __construct(private RestClient $restClient)
    {
    }

    public function uploadLayDeputyShip(mixed $compressedData)
    {
        return $this->restClient->setTimeout(600)->post(self::UPLOAD_LAY_DEPUTYSHIP_ENDPOINT, $compressedData);
    }
}
