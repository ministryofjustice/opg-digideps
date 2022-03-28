<?php

declare(strict_types=1);

namespace App\v2\Registration\DTO;

use DateTime;

class OrgDeputyshipDto
{
    /** @var string */
    private $caseNumber;
    private $clientFirstname;
    private $clientLastname;
    private $clientAddress1;
    private $clientAddress2;
    private $clientPostCode;
    private $deputyUUID;
    private $deputyFirstname;
    private $deputyLastname;
    private $deputyEmail;
    private $deputyAddress1;
    private $deputyAddress2;
    private $deputyPostcode;
    private $reportType;

    /** @var string|null */
    private $clientAddress3;
    private $clientAddress4;
    private $clientAddress5;
    private $deputyAddress3;
    private $deputyAddress4;
    private $deputyAddress5;

    /** @var DateTime|null */
    private $clientDateOfBirth;
    private $courtDate;
    private $reportStartDate;
    private $reportEndDate;

    public function getDeputyEmail(): ?string
    {
        return $this->deputyEmail;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setDeputyEmail(?string $deputyEmail): self
    {
        $this->deputyEmail = $deputyEmail;

        return $this;
    }

    public function getDeputyUUID(): string
    {
        return $this->deputyUUID;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setDeputyUUID(string $deputyUUID): self
    {
        $this->deputyUUID = $deputyUUID;

        return $this;
    }

    public function getDeputyNumber(): string
    {
        return $this->deputyNumber;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setDeputyNumber(string $deputyNumber): self
    {
        $this->deputyNumber = $deputyNumber;

        return $this;
    }

    public function getDeputyFirstname(): ?string
    {
        return $this->deputyFirstname;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setDeputyFirstname(?string $deputyFirstname): self
    {
        $this->deputyFirstname = $deputyFirstname;

        return $this;
    }

    public function getDeputyLastname(): string
    {
        return $this->deputyLastname;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setDeputyLastname(string $deputyLastname): self
    {
        $this->deputyLastname = $deputyLastname;

        return $this;
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
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
     * @return OrgDeputyshipDto
     */
    public function setCourtDate(?DateTime $courtDate): self
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
     * @return OrgDeputyshipDto
     */
    public function setClientLastname(string $clientLastname): self
    {
        $this->clientLastname = $clientLastname;

        return $this;
    }

    public function getClientAddress1(): ?string
    {
        return $this->clientAddress1;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setClientAddress1(?string $clientAddress1): self
    {
        $this->clientAddress1 = $clientAddress1;

        return $this;
    }

    public function getClientAddress2(): ?string
    {
        return $this->clientAddress2;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setClientAddress2(?string $clientAddress2): self
    {
        $this->clientAddress2 = $clientAddress2;

        return $this;
    }

    public function getClientAddress3(): ?string
    {
        return $this->clientAddress3;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setClientAddress3(?string $clientAddress3): self
    {
        $this->clientAddress3 = $clientAddress3;

        return $this;
    }

    public function getClientAddress4(): ?string
    {
        return $this->clientAddress4;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setClientAddress4(?string $clientAddress4): self
    {
        $this->clientAddress4 = $clientAddress4;

        return $this;
    }

    public function getClientAddress5(): ?string
    {
        return $this->clientAddress5;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setClientAddress5(?string $clientAddress5): self
    {
        $this->clientAddress5 = $clientAddress5;

        return $this;
    }

    public function getClientPostCode(): ?string
    {
        return $this->clientPostCode;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setClientPostCode(?string $clientPostCode): self
    {
        $this->clientPostCode = $clientPostCode;

        return $this;
    }

    public function getClientDateOfBirth(): ?DateTime
    {
        return $this->clientDateOfBirth;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setClientDateOfBirth(?DateTime $clientDateOfBirth): self
    {
        $this->clientDateOfBirth = $clientDateOfBirth;

        return $this;
    }

    public function getDeputyAddress1(): string
    {
        return $this->deputyAddress1;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setDeputyAddress1(string $deputyAddress1): self
    {
        $this->deputyAddress1 = $deputyAddress1;

        return $this;
    }

    public function getDeputyPostcode(): string
    {
        return $this->deputyPostcode;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setDeputyPostcode(string $deputyPostcode): self
    {
        $this->deputyPostcode = $deputyPostcode;

        return $this;
    }

    public function getReportType(): string
    {
        return $this->reportType;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setReportType(string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
    }

    public function getReportStartDate(): ?DateTime
    {
        return $this->reportStartDate;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setReportStartDate(?DateTime $reportStartDate): self
    {
        $this->reportStartDate = $reportStartDate;

        return $this;
    }

    public function getReportEndDate(): ?DateTime
    {
        return $this->reportEndDate;
    }

    /**
     * @return OrgDeputyshipDto
     */
    public function setReportEndDate(?DateTime $reportEndDate): self
    {
        $this->reportEndDate = $reportEndDate;

        return $this;
    }

    public function getDeputyAddressNumber(): ?string
    {
        return $this->deputyAddressNumber;
    }

    public function setDeputyAddressNumber(?string $deputyAddressNumber): OrgDeputyshipDto
    {
        $this->deputyAddressNumber = $deputyAddressNumber;

        return $this;
    }

    public function getDeputyAddress2(): string
    {
        return $this->deputyAddress2;
    }

    public function setDeputyAddress2(string $deputyAddress2): OrgDeputyshipDto
    {
        $this->deputyAddress2 = $deputyAddress2;

        return $this;
    }

    public function getDeputyAddress3(): ?string
    {
        return $this->deputyAddress3;
    }

    public function setDeputyAddress3(?string $deputyAddress3): OrgDeputyshipDto
    {
        $this->deputyAddress3 = $deputyAddress3;

        return $this;
    }

    public function getDeputyAddress4(): ?string
    {
        return $this->deputyAddress4;
    }

    public function setDeputyAddress4(?string $deputyAddress4): OrgDeputyshipDto
    {
        $this->deputyAddress4 = $deputyAddress4;

        return $this;
    }

    public function getDeputyAddress5(): ?string
    {
        return $this->deputyAddress5;
    }

    public function setDeputyAddress5(?string $deputyAddress5): OrgDeputyshipDto
    {
        $this->deputyAddress5 = $deputyAddress5;

        return $this;
    }
}
