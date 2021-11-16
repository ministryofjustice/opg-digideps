<?php

namespace App\Entity\Report;

class UnsubmittedSection
{
    /**
     * UnsubmittedSection constructor.
     * @param string $id
     * @param bool   $present
     */
    public function __construct(
        /**
         * Store section identifier
         */
        private $id,
        /**
         * Store checkbox value
         */
        private $present
    )
    {
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
