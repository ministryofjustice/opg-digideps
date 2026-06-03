<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Predis\Client;

// create a simple predis Mock to just return keys
class PredisMock extends Client
{
    private array $data = [];

    /** @var array<array> $calls */
    public array $calls = [];

    public function set(string $key, mixed $value): void
    {
        $this->calls[] = ['set', $key];
        $this->data[$key] = $value;
    }

    public function get(string $key): mixed
    {
        $this->calls[] = ['get', $key];
        return $this->data[$key] ?? null;
    }

    public function expire(string $key, int $seconds): void
    {
        $this->calls[] = ['expire', $key, $seconds];
        if (!isset($this->data[$key])) {
            throw new \InvalidArgumentException("key $key not set");
        }
    }

    public function __call($commandID, $arguments)
    {
        throw new \InvalidArgumentException("PredisMock: Method $commandID not implemented");
    }
}
