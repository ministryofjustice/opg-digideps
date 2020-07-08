<?php declare(strict_types=1);


namespace AppBundle\Service\Time;


use DateTime;

Class Clock implements ClockInterface
{
    /**
     * Get the current time.
     * @return \DateTime
     * @throws \Exception
     */
    public function now(){
        return new DateTime();
    }
}
