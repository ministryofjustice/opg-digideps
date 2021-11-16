<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

class Logger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function notice(string $message, array $context)
    {
        $this->logger->notice($message, $context);
    }

    public function warning(string $message, ?array $context = [])
    {
        $this->logger->warning($message, $context);
    }

    public function debug(string $message, ?array $context = [])
    {
        $this->logger->debug($message, $context);
    }
}
