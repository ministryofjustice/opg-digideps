<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section\Link;

final readonly class RoutedUrl
{
    public function __construct(
        public string $name,
        public array $parameters
    ) {
    }

    public static function snakeCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input) ?? '');
    }
}
