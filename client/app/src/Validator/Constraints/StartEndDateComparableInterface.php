<?php

namespace App\Validator\Constraints;

interface StartEndDateComparableInterface
{
    /**
     * @return \DateTime|null
     */
    public function getStartDate();

    /**
     * @return \DateTime|null
     */
    public function getEndDate();
}
