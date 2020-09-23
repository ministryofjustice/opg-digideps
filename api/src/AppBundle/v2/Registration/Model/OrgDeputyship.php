<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Model;

class OrgDeputyship
{
    private $isValid;

    public function isValid()
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }
}
