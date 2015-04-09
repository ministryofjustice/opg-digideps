<?php

namespace AppBundle\Service\Availability;

abstract class AvailabilityAbstract
{

    /**
     * @var boolean
     */
    protected $isHealthy;

    /**
     * @var string
     */
    protected $errors;


    /**
     * @return boolean
     */
    public function isHealthy()
    {
        return $this->isHealthy;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'healthy' => $this->isHealthy(),
            'errors' => $this->getErrors(),
        ];
    }

}