<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait WaitOnItTrait
{
    /**
     * Retry a callable until it succeeds or timeout.
     *
     * @template T
     * @param callable():T $lambda
     * @return T
     * @throws \Exception
     */
    public function waitOnIt(callable $lambda, string $funcName, int $wait = 10)
    {
        for ($i = 0; $i < $wait; $i++) {
            try {
                $result = $lambda();
                if ($result) {
                    return $result; // return whatever the lambda produced
                }
            } catch (\Throwable $e) {
                // ignore and retry
            }
            sleep(1);
        }

        throw new \Exception("Wait on it function for {$funcName} timed out after {$wait} seconds");
    }
}
