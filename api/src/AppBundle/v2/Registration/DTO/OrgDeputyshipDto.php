<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\DTO;

use DateTime;

class OrgDeputyshipDto
{
    /** @var string */
    private $deputyNumber;
    private $deputyLastname;
    private $deputyAddressNumber;
    private $deputyAddress1;
    private $deputyAddress2;
    private $caseNumber;
    private $clientFirstname;
    private $clientLastname;
    private $clientAddress1;
    private $clientAddress2;
    private $clientCounty;
    private $clientPostCode;
    // Write test in assembler on working out reportType
    private $reportType;

    /** @var string|null */
    private $deputyEmail;
    private $deputyAddress3;
    private $deputyAddress4;
    private $deputyAddress5;
    private $deputyPostcode;
    private $deputyFirstname;


    /** @var DateTime|null */
    private $clientDateOfBirth;
    private $courtDate;
    private $reportStartDate;
    private $reportEndDate;

    /**
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->getDeputyEmail()) &&
            !empty($this->getCaseNumber()) &&
            !empty($this->getReportType()) &&
            !empty($this->getReportStartDate());
    }

    /**
     * @return string|null
     */
    public function getDeputyEmail(): ?string
    {
        return $this->deputyEmail;
    }

    /**
     * @param string|null $deputyEmail
     * @return OrgDeputyshipDto
     */
    public function setDeputyEmail(?string $deputyEmail): self
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
     * @return string|null
     */
    public function getDeputyFirstname(): ?string
    {
        return $this->deputyFirstname;
    }

    /**
     * @param string|null $deputyFirstname
     * @return OrgDeputyshipDto
     */
    public function setDeputyFirstname(?string $deputyFirstname): self
    {
        $this->deputyFirstname = $deputyFirstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyLastname(): string
    {
        return $this->deputyLastname;
    }

    /**
     * @param string $deputyLastname
     * @return OrgDeputyshipDto
     */
    public function setDeputyLastname(string $deputyLastname): self
    {
        $this->deputyLastname = $deputyLastname;

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
     * @return string|null
     */
    public function getClientAddress1(): ?string
    {
        return $this->clientAddress1;
    }

    /**
     * @param string|null $clientAddress1
     * @return OrgDeputyshipDto
     */
    public function setClientAddress1(?string $clientAddress1): self
    {
        $this->clientAddress1 = $clientAddress1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientAddress2(): ?string
    {
        return $this->clientAddress2;
    }

    /**
     * @param string|null $clientAddress2
     * @return OrgDeputyshipDto
     */
    public function setClientAddress2(?string $clientAddress2): self
    {
        $this->clientAddress2 = $clientAddress2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientCounty(): ?string
    {
        return $this->clientCounty;
    }

    /**
     * @param string|null $clientCounty
     * @return OrgDeputyshipDto
     */
    public function setClientCounty(?string $clientCounty): self
    {
        $this->clientCounty = $clientCounty;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientPostCode(): ?string
    {
        return $this->clientPostCode;
    }

    /**
     * @param string|null $clientPostCode
     * @return OrgDeputyshipDto
     */
    public function setClientPostCode(?string $clientPostCode): self
    {
        $this->clientPostCode = $clientPostCode;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getClientDateOfBirth(): ?DateTime
    {
        return $this->clientDateOfBirth;
    }

    /**
     * @param DateTime|null $clientDateOfBirth
     * @return OrgDeputyshipDto
     */
    public function setClientDateOfBirth(?DateTime $clientDateOfBirth): self
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

    /**
     * @return string
     */
    public function getReportType(): string
    {
        return $this->reportType;
    }

    /**
     * @param string $reportType
     * @return OrgDeputyshipDto
     */
    public function setReportType(string $reportType): self
    {
        $this->reportType = $reportType;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getReportStartDate(): DateTime
    {
        return $this->reportStartDate;
    }

    /**
     * @param DateTime $reportStartDate
     * @return OrgDeputyshipDto
     */
    public function setReportStartDate(DateTime $reportStartDate): self
    {
        $this->reportStartDate = $reportStartDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getReportEndDate(): DateTime
    {
        return $this->reportEndDate;
    }

    /**
     * @param DateTime $reportEndDate
     * @return OrgDeputyshipDto
     */
    public function setReportEndDate(DateTime $reportEndDate): self
    {
        $this->reportEndDate = $reportEndDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyAddressNumber(): string
    {
        return $this->deputyAddressNumber;
    }

    /**
     * @param string $deputyAddressNumber
     * @return OrgDeputyshipDto
     */
    public function setDeputyAddressNumber(string $deputyAddressNumber): OrgDeputyshipDto
    {
        $this->deputyAddressNumber = $deputyAddressNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyAddress2(): string
    {
        return $this->deputyAddress2;
    }

    /**
     * @param string $deputyAddress2
     * @return OrgDeputyshipDto
     */
    public function setDeputyAddress2(string $deputyAddress2): OrgDeputyshipDto
    {
        $this->deputyAddress2 = $deputyAddress2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeputyAddress3(): ?string
    {
        return $this->deputyAddress3;
    }

    /**
     * @param string|null $deputyAddress3
     * @return OrgDeputyshipDto
     */
    public function setDeputyAddress3(?string $deputyAddress3): OrgDeputyshipDto
    {
        $this->deputyAddress3 = $deputyAddress3;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeputyAddress4(): ?string
    {
        return $this->deputyAddress4;
    }

    /**
     * @param string|null $deputyAddress4
     * @return OrgDeputyshipDto
     */
    public function setDeputyAddress4(?string $deputyAddress4): OrgDeputyshipDto
    {
        $this->deputyAddress4 = $deputyAddress4;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeputyAddress5(): ?string
    {
        return $this->deputyAddress5;
    }

    /**
     * @param string|null $deputyAddress5
     * @return OrgDeputyshipDto
     */
    public function setDeputyAddress5(?string $deputyAddress5): OrgDeputyshipDto
    {
        $this->deputyAddress5 = $deputyAddress5;
        return $this;
    }
}
