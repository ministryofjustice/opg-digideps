<?php

namespace AppBundle\v2\DTO;

class NdrDto
{
    /** @var int */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return NdrDto
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
