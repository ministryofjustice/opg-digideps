<?php

namespace App\Entity\Report;

class UnsubmittedSection
{
    /**
     * Store section identifier.
     *
     * @var string
     */
    private $id;

    /**
     * Store checkbox value.
     *
     * @var bool
     */
    private $present;

    /**
     * UnsubmittedSection constructor.
     *
     * @param string $id
     * @param bool   $present
     */
    public function __construct($id, $present)
    {
        $this->id = $id;
        $this->present = $present;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isPresent()
    {
        return $this->present;
    }

    /**
     * @param bool $present
     */
    public function setPresent($present)
    {
        $this->present = $present;
    }
}
