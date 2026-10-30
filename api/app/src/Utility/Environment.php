<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Utility;

final readonly class Environment
{
    public function __construct(
        public bool $isProduction,
        public string $validationKeyPepper,
    ) {
    }

    public static function fromEnv(string $workspace, string $validationKeyPepper): Environment
    {
        return new Environment($workspace === 'production', $validationKeyPepper);
    }
}
