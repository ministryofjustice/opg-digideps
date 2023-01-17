<?php

declare(strict_types=1);

namespace App\v2\Registration\DTO;

class OrgDeputyshipDto
{
    private string $caseNumber;
    private string $clientFirstname;
    private string $clientLastname;
    private string $clientAddress1;
    private string $clientAddress2;
    private string $clientPostCode;
    private string $deputyUid;
    private string $deputyFirstname;
    private string $deputyLastname;
    private ?string $deputyEmail;
    private string $deputyAddress1;
    private string $deputyAddress2;
    private string $deputyPostcode;
    private string $organisationName;
    private string $reportType;
    private ?string $hybrid;

    private ?string $clientAddress3;
    private ?string $clientAddress4;
    private ?string $clientAddress5;
    private ?string $deputyAddress3;
    private ?string $deputyAddress4;
    private ?string $deputyAddress5;

    private ?\DateTime $clientDateOfBirth;
    private ?\DateTime $courtDate;
    private ?\DateTime $reportStartDate;
    private ?\DateTime $reportEndDate;

    public const SINGLE_TYPE = 'SINGLE';
    public const HYBRID_TYPE = 'HYBRID';
    public const DUAL_TYPE = 'DUAL';

    public function getDeputyEmail(): ?string
    {
        return $this->deputyEmail;
    }

    public function setDeputyEmail(?string $deputyEmail): self
    {
        $this->deputyEmail = $deputyEmail;

        return $this;
    }

    public function getDeputyUid(): string
    {
        return $this->deputyUid;
    }

    public function setDeputyUid(string $deputyUid): self
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

    public function getDeputyFirstname(): ?string
    {
        return $this->deputyFirstname;
    }

    public function setDeputyFirstname(?string $deputyFirstname): self
    {
        $this->deputyFirstname = $deputyFirstname;

        return $this;
    }

    public function getDeputyLastname(): string
    {
        return $this->deputyLastname;
    }

    public function setDeputyLastname(string $deputyLastname): self
    {
        $this->deputyLastname = $deputyLastname;

        return $this;
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCourtDate()
    {
        return $this->courtDate;
    }

    public function setCourtDate(?\DateTime $courtDate): self
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

    public function setClientLastname(string $clientLastname): self
    {
        $this->clientLastname = $clientLastname;

        return $this;
    }

    public function getClientAddress1(): ?string
    {
        return $this->clientAddress1;
    }

    public function setClientAddress1(?string $clientAddress1): self
    {
        $this->clientAddress1 = $clientAddress1;

        return $this;
    }

    public function getClientAddress2(): ?string
    {
        return $this->clientAddress2;
    }

    public function setClientAddress2(?string $clientAddress2): self
    {
        $this->clientAddress2 = $clientAddress2;

        return $this;
    }

    public function getClientAddress3(): ?string
    {
        return $this->clientAddress3;
    }

    public function setClientAddress3(?string $clientAddress3): self
    {
        $this->clientAddress3 = $clientAddress3;

        return $this;
    }

    public function getClientAddress4(): ?string
    {
        return $this->clientAddress4;
    }

    public function setClientAddress4(?string $clientAddress4): self
    {
        $this->clientAddress4 = $clientAddress4;

        return $this;
    }

    public function getClientAddress5(): ?string
    {
        return $this->clientAddress5;
    }

    public function setClientAddress5(?string $clientAddress5): self
    {
        $this->clientAddress5 = $clientAddress5;

        return $this;
    }

    public function getClientPostCode(): ?string
    {
        return $this->clientPostCode;
    }

    public function setClientPostCode(?string $clientPostCode): self
    {
        $this->clientPostCode = $clientPostCode;

        return $this;
    }

    public function getClientDateOfBirth(): ?\DateTime
    {
        return $this->clientDateOfBirth;
    }

    public function setClientDateOfBirth(?\DateTime $clientDateOfBirth): self
    {
        $this->clientDateOfBirth = $clientDateOfBirth;

        return $this;
    }

    public function getDeputyAddress1(): string
    {
        return $this->deputyAddress1;
    }

    public function setDeputyAddress1(string $deputyAddress1): self
    {
        $this->deputyAddress1 = $deputyAddress1;

        return $this;
    }

    public function getDeputyPostcode(): string
    {
        return $this->deputyPostcode;
    }

    public function setDeputyPostcode(string $deputyPostcode): self
    {
        $this->deputyPostcode = $deputyPostcode;

        return $this;
    }

    public function getReportType(): string
    {
        return $this->reportType;
    }

    public function setReportType(string $reportType): self
    {
        $this->reportType = $reportType;

        return $this;
    }

    public function getReportStartDate(): ?\DateTime
    {
        return $this->reportStartDate;
    }

    public function setReportStartDate(?\DateTime $reportStartDate): self
    {
        $this->reportStartDate = $reportStartDate;

        return $this;
    }

    public function getReportEndDate(): ?\DateTime
    {
        return $this->reportEndDate;
    }

    public function setReportEndDate(?\DateTime $reportEndDate): self
    {
        $this->reportEndDate = $reportEndDate;

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

    public function getOrganisationName(): ?string
    {
        return $this->organisationName;
    }

    public function setOrganisationName(?string $organisationName): OrgDeputyshipDto
    {
        $this->organisationName = $organisationName;

        return $this;
    }

    public function deputyIsAnOrganisation(): bool
    {
        return $this->getOrganisationName() && empty($this->getDeputyFirstname());
    }

    public function getHybrid(): ?string
    {
        return $this->hybrid;
    }

    public function setHybrid(?string $hybrid): self
    {
        $this->hybrid = $hybrid;

        return $this;
    }
}
