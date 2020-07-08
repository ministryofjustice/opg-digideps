<?php declare(strict_types=1);


namespace AppBundle\Service\Time;


interface ClockInterface
{
    /**
     * Get the current time.
     * @return \DateTime
     */
    public function now();
}
