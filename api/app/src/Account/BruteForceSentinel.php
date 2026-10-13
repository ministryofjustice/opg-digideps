<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Account;

use OPG\Digideps\Common\Account\ValidationKey;
use Predis\Client;
use Psr\Log\LoggerInterface;

final readonly class BruteForceSentinel
{
    private const int MIN_SECONDS_APART = 3600;

    public function __construct(
        private Client $predis,
        private LoggerInterface $logger
    ) {
    }

    public function checkAndRegisterAttempt(ValidationKey $validationKey): bool
    {
        $predisKey = "BruteForceSentinel::{$validationKey->value}";
        $value = (int)$this->predis->get($predisKey);
        $now = (int)round(microtime(true));
        $allow = $now - $value > self::MIN_SECONDS_APART;
        $this->predis->set($predisKey, $now);
        if (!$allow) {
            $this->logger->warning('BruteForceSentinel blocked password set via token');
        }
        return $allow;
    }
}
