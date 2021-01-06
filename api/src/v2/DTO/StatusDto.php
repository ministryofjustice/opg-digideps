<?php

namespace AppBundle\v2\DTO;

class StatusDto
{
    /** @var string */
    private $status;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return StatusDto
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}
