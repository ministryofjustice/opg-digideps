<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\DTO;

use DateTime;

class OrgDeputyshipDto
{
    /** @var string */
    private $deputyEmail;
    private $deputyNumber;
    private $firstname;
    private $lastname;
    private $deputyAddress1;
    private $deputyPostcode;
    private $caseNumber;
    private $clientFirstname;
    private $clientLastname;
    private $clientAddress1;
    private $clientAddress2;
    private $clientAddress3;
    private $clientPostCode;

    /** @var DateTime */
    private $clientDateOfBirth;
    private $courtDate;

    /**
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->getDeputyEmail());
    }

    /**
     * @return string
     */
    public function getDeputyEmail(): string
    {
        return $this->deputyEmail;
    }

    /**
     * @param string $deputyEmail
     * @return OrgDeputyshipDto
     */
    public function setDeputyEmail(string $deputyEmail): self
    {
        $this->deputyEmail = $deputyEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyNumber(): string
    {
        return $this->deputyNumber;
    }

    /**
     * @param string $deputyNumber
     * @return OrgDeputyshipDto
     */
    public function setDeputyNumber(string $deputyNumber): self
    {
        $this->deputyNumber = $deputyNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return OrgDeputyshipDto
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return OrgDeputyshipDto
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     * @return OrgDeputyshipDto
     */
    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCourtDate()
    {
        return $this->courtDate;
    }

    /**
     * @param DateTime $courtDate
     * @return OrgDeputyshipDto
     */
    public function setCourtDate(DateTime $courtDate): self
    {
        $this->courtDate = $courtDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientFirstname()
    {
        return $this->clientFirstname;
    }

    /**
     * @param string $clientFirstname
     * @return OrgDeputyshipDto
     */
    public function setClientFirstname(string $clientFirstname): self
    {
        $this->clientFirstname = $clientFirstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientLastname()
    {
        return $this->clientLastname;
    }

    /**
     * @param string $clientLastname
     * @return OrgDeputyshipDto
     */
    public function setClientLastname(string $clientLastname): self
    {
        $this->clientLastname = $clientLastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientAddress1(): string
    {
        return $this->clientAddress1;
    }

    /**
     * @param string $clientAddress1
     * @return OrgDeputyshipDto
     */
    public function setClientAddress1(string $clientAddress1): self
    {
        $this->clientAddress1 = $clientAddress1;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientAddress2(): string
    {
        return $this->clientAddress2;
    }

    /**
     * @param string $clientAddress2
     * @return OrgDeputyshipDto
     */
    public function setClientAddress2(string $clientAddress2): self
    {
        $this->clientAddress2 = $clientAddress2;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientAddress3(): string
    {
        return $this->clientAddress3;
    }

    /**
     * @param string $clientAddress3
     * @return OrgDeputyshipDto
     */
    public function setClientAddress3(string $clientAddress3): self
    {
        $this->clientAddress3 = $clientAddress3;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientPostCode(): string
    {
        return $this->clientPostCode;
    }

    /**
     * @param string $clientPostCode
     * @return OrgDeputyshipDto
     */
    public function setClientPostCode(string $clientPostCode): self
    {
        $this->clientPostCode = $clientPostCode;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getClientDateOfBirth(): DateTime
    {
        return $this->clientDateOfBirth;
    }

    /**
     * @param DateTime $clientDateOfBirth
     * @return OrgDeputyshipDto
     */
    public function setClientDateOfBirth(DateTime $clientDateOfBirth): self
    {
        $this->clientDateOfBirth = $clientDateOfBirth;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyAddress1(): string
    {
        return $this->deputyAddress1;
    }

    /**
     * @param string $deputyAddress1
     * @return OrgDeputyshipDto
     */
    public function setDeputyAddress1(string $deputyAddress1): self
    {
        $this->deputyAddress1 = $deputyAddress1;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyPostcode(): string
    {
        return $this->deputyPostcode;
    }

    /**
     * @param string $deputyPostcode
     * @return OrgDeputyshipDto
     */
    public function setDeputyPostcode(string $deputyPostcode): self
    {
        $this->deputyPostcode = $deputyPostcode;
        return $this;
    }
}
