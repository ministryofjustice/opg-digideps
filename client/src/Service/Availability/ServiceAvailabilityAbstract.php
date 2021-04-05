<?php

namespace App\Service\Availability;

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
     * @var
     */
    protected $customMessage;

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

    public function getCustomMessage()
    {
        return $this->customMessage;
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

    abstract public function ping();
}
