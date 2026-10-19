<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup\Model;

final readonly class Order
{
    public function __construct(
        public Client $client,
        public int $orderId,
        public string $orderUid,
        public \DateTimeImmutable $madeDate,
        public bool $open
    ) {
    }
}
