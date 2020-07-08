<?php declare(strict_types=1);


namespace AppBundle\Service\Time;


use DateTime;

Class FakeClock implements ClockInterface
{
    private $time;

    public function __construct(DateTime $now)
    {
        $this->time = $now;
    }

    /**
     * Get the current time.
     * @return \DateTime
     * @throws \Exception
     */
    public function now(){
        return $this->time;
    }
}
