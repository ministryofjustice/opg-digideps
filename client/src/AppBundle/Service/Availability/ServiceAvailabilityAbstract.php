<?php

namespace AppBundle\Service\Availability;

/**
 * TODO change into an interface with
 * isHealthy
 * getErrors
 */
abstract class ServiceAvailabilityAbstract
{
    /**
     * @var bool
     */
    protected $isHealthy;

    /**
     * @var string
     */
    protected $errors;

    /**
     * @return bool
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

    /**
     * @return string
     */
    abstract public function getName();
}
