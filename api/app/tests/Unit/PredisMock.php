<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit;

use Predis\Client;

// because Predis\Client uses magic methods which phpunit can't mock
class PredisMock extends Client
{
    public function get(): ?string
    {
        return '';
    }
}
