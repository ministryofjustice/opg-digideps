<?php

namespace AppBundle\Entity\Report;

class ProfServiceFeeCurrent extends ProfServiceFee
{
    public function getFeeTypeId()
    {
        return 'current';
    }
}
