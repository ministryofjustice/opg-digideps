<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CourtOrder;
use App\Service\Client\RestClient;
use Psr\Log\LoggerInterface;

class CourtOrderService
{
    public function __construct(
        private readonly RestClient $restClient
    ) {
    }

    public function getByUid(string $uid): CourtOrder
    {
        return $this->restClient->getAndDeserialize(sprintf('v2/courtorder/%s', $uid), CourtOrder::class);
    }
}
