<?php

namespace OPG\Digideps\Frontend\Entity\Report;

class ProfServiceFeeCurrent extends ProfServiceFee
{
    public function getFeeTypeId()
    {
        return 'current';
    }
}
