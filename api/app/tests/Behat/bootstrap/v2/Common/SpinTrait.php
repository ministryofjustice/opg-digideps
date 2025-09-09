<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait SpinTrait
{
    /**
     * Retry a callable until it succeeds or timeout.
     *
     * @template T
     * @param callable():T $lambda
     * @return T
     * @throws \Exception
     */
    public function spin(callable $lambda, int $wait = 10)
    {
        for ($i = 0; $i < $wait; $i++) {
            try {
                $result = $lambda();
                if ($result) {
                    return $result; // return whatever the lambda produced
                }
            } catch (\Throwable $e) {
                // ignore and retry
                file_put_contents('php://stderr', print_r('DOING A SPIN!', true));
            }
            sleep(1);
        }

        throw new \Exception("Spin function timed out after {$wait} seconds");
    }
}
