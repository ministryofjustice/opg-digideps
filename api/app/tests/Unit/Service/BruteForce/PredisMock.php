<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\BruteForce;

use Predis\Client;

// create a simple predis Mock to just return keys
class PredisMock extends Client
{
    private array $data = [];
    public array $calls = [];

    public function set($key, $value): void
    {
        $this->calls[] = ['set', $key];
        $this->data[$key] = $value;
    }

    public function get($key)
    {
        $this->calls[] = ['get', $key];
        return $this->data[$key] ?? null;
    }

    public function expire($key, $seconds): void
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
