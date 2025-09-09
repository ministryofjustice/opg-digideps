<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait SpinTrait
{
    public function spin(callable $lambda, int $wait = 10): void
    {
        for ($i = 0; $i < $wait; $i++) {
            try {
                if ($lambda()) {
                    return;
                }
            } catch (\Throwable $e) {
                // ignore and retry
            }
            sleep(1);
        }

        throw new \Exception("Spin function timed out after {$wait} seconds");
    }
}
