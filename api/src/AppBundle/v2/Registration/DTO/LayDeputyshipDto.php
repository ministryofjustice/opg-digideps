<?php

namespace AppBundle\v2\Registration\DTO;

class LayDeputyshipDto
{
    /** @var string */
    private $caseNumber;

    /** @var string */
    private $clientSurname;

    /** @var string */
    private $deputyNumber;

    /** @var string */
    private $deputySurname;

    /** @var string */
    private $deputyPostcode;

    /** @var string */
    private $typeOfReport;

    /** @var string */
    private $corref;

    /** @var bool */
    private $isNdrEnabled;

    /** @return string */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     * @return LayDeputyshipDto
     */
    public function setCaseNumber(string $caseNumber): LayDeputyshipDto
    {
        $this->caseNumber = $caseNumber;
        return $this;
    }

    /** @return string */
    public function getClientSurname(): string
    {
        return $this->clientSurname;
    }

    /**
     * @param mixed $clientSurname
     * @return LayDeputyshipDto
     */
    public function setClientSurname($clientSurname): LayDeputyshipDto
    {
        $this->clientSurname = $clientSurname;
        return $this;
    }

    /** @return string */
    public function getDeputyNumber(): string
    {
        return $this->deputyNumber;
    }

    /**
     * @param string $deputyNumber
     * @return LayDeputyshipDto
     */
    public function setDeputyNumber(string $deputyNumber): LayDeputyshipDto
    {
        $this->deputyNumber = $deputyNumber;
        return $this;
    }

    /** @return string */
    public function getDeputySurname(): string
    {
        return $this->deputySurname;
    }

    /**
     * @param string $deputySurname
     * @return LayDeputyshipDto
     */
    public function setDeputySurname(string $deputySurname): LayDeputyshipDto
    {
        $this->deputySurname = $deputySurname;
        return $this;
    }

    /** @return string */
    public function getDeputyPostcode(): string
    {
        return $this->deputyPostcode;
    }

    /**
     * @param string $deputyPostcode
     * @return LayDeputyshipDto
     */
    public function setDeputyPostcode(string $deputyPostcode): LayDeputyshipDto
    {
        $this->deputyPostcode = $deputyPostcode;
        return $this;
    }

    /** @return string */
    public function getTypeOfReport(): string
    {
        return $this->typeOfReport;
    }

    /**
     * @param string $typeOfReport
     * @return LayDeputyshipDto
     */
    public function setTypeOfReport(string $typeOfReport): LayDeputyshipDto
    {
        $this->typeOfReport = $typeOfReport;
        return $this;
    }

    /** @return string */
    public function getCorref(): string
    {
        return $this->corref;
    }

    /**
     * @param string $corref
     * @return LayDeputyshipDto
     */
    public function setCorref(string $corref): LayDeputyshipDto
    {
        $this->corref = $corref;
        return $this;
    }

    /** @return bool */
    public function isNdrEnabled(): bool
    {
        return $this->isNdrEnabled;
    }

    /**
     * @param bool $isNdrEnabled
     * @return LayDeputyshipDto
     */
    public function setIsNdrEnabled(bool $isNdrEnabled): LayDeputyshipDto
    {
        $this->isNdrEnabled = $isNdrEnabled;
        return $this;
    }
}
