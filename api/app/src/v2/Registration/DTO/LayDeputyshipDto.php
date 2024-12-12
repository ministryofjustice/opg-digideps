<?php

declare(strict_types=1);

namespace App\v2\Registration\DTO;

class LayDeputyshipDto
{
    private ?string $caseNumber;
    private ?string $clientFirstname;
    private ?string $clientSurname;
    private ?string $clientAddress1;
    private ?string $clientAddress2;
    private ?string $clientAddress3;
    private ?string $clientAddress4;
    private ?string $clientAddress5;
    private ?string $clientPostcode;
    private ?string $deputyFirstname;
    private ?string $deputySurname;
    private ?string $deputyUid;
    private ?string $deputyAddress1;
    private ?string $deputyAddress2;
    private ?string $deputyAddress3;
    private ?string $deputyAddress4;
    private ?string $deputyAddress5;
    private ?string $deputyPostcode;
    private ?bool $isCoDeputy;
    private ?bool $isNdrEnabled;
    private ?\DateTime $orderDate;
    private ?string $orderType;
    private ?string $typeOfReport;
    private ?string $hybrid;

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): LayDeputyshipDto
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getClientSurname(): string
    {
        return $this->clientSurname;
    }

    public function setClientSurname($clientSurname): LayDeputyshipDto
    {
        $this->clientSurname = $clientSurname;

        return $this;
    }

    public function getDeputyUid(): string
    {
        return $this->deputyUid;
    }

    public function setDeputyUid(string $deputyUid): LayDeputyshipDto
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

    public function getDeputyFirstname(): string
    {
        return $this->deputyFirstname;
    }

    public function setDeputyFirstname(string $deputyFirstname): LayDeputyshipDto
    {
        $this->deputyFirstname = $deputyFirstname;

        return $this;
    }

    public function getDeputySurname(): string
    {
        return $this->deputySurname;
    }

    public function setDeputySurname(string $deputySurname): LayDeputyshipDto
    {
        $this->deputySurname = $deputySurname;

        return $this;
    }

    public function getDeputyPostcode(): string
    {
        return $this->deputyPostcode;
    }

    public function setDeputyPostcode(string $deputyPostcode): LayDeputyshipDto
    {
        $this->deputyPostcode = $deputyPostcode;

        return $this;
    }

    public function getTypeOfReport(): string
    {
        return $this->typeOfReport;
    }

    public function setTypeOfReport(string $typeOfReport): LayDeputyshipDto
    {
        $this->typeOfReport = $typeOfReport;

        return $this;
    }

    public function isNdrEnabled(): bool
    {
        return $this->isNdrEnabled;
    }

    public function setIsNdrEnabled(bool $isNdrEnabled): LayDeputyshipDto
    {
        $this->isNdrEnabled = $isNdrEnabled;

        return $this;
    }

    public function getOrderDate(): \DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTime $orderDate): LayDeputyshipDto
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getDeputyAddress1(): ?string
    {
        return $this->deputyAddress1;
    }

    public function setDeputyAddress1(?string $deputyAddress1): LayDeputyshipDto
    {
        $this->deputyAddress1 = $deputyAddress1;

        return $this;
    }

    public function getDeputyAddress2(): ?string
    {
        return $this->deputyAddress2;
    }

    public function setDeputyAddress2(?string $deputyAddress2): LayDeputyshipDto
    {
        $this->deputyAddress2 = $deputyAddress2;

        return $this;
    }

    public function getDeputyAddress3(): ?string
    {
        return $this->deputyAddress3;
    }

    public function setDeputyAddress3(?string $deputyAddress3): LayDeputyshipDto
    {
        $this->deputyAddress3 = $deputyAddress3;

        return $this;
    }

    public function getDeputyAddress4(): ?string
    {
        return $this->deputyAddress4;
    }

    public function setDeputyAddress4(?string $deputyAddress4): LayDeputyshipDto
    {
        $this->deputyAddress4 = $deputyAddress4;

        return $this;
    }

    public function getDeputyAddress5(): ?string
    {
        return $this->deputyAddress5;
    }

    public function setDeputyAddress5(?string $deputyAddress5): LayDeputyshipDto
    {
        $this->deputyAddress5 = $deputyAddress5;

        return $this;
    }

    public function getIsCoDeputy(): ?bool
    {
        return $this->isCoDeputy;
    }

    public function setIsCoDeputy(?bool $isCoDeputy): LayDeputyshipDto
    {
        $this->isCoDeputy = $isCoDeputy;

        return $this;
    }

    public function getOrderType(): ?string
    {
        return $this->orderType;
    }

    public function setOrderType(?string $orderType): LayDeputyshipDto
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getHybrid(): ?string
    {
        return $this->hybrid;
    }

    public function setHybrid(?string $hybrid): LayDeputyshipDto
    {
        $this->hybrid = $hybrid;

        return $this;
    }

    public function getClientFirstname(): ?string
    {
        return $this->clientFirstname;
    }

    public function setClientFirstname(?string $clientFirstname): LayDeputyshipDto
    {
        $this->clientFirstname = $clientFirstname;

        return $this;
    }

    public function getClientAddress1(): ?string
    {
        return $this->clientAddress1;
    }

    public function setClientAddress1(?string $clientAddress1): LayDeputyshipDto
    {
        $this->clientAddress1 = $clientAddress1;

        return $this;
    }

    public function getClientAddress2(): ?string
    {
        return $this->clientAddress2;
    }

    public function setClientAddress2(?string $clientAddress2): LayDeputyshipDto
    {
        $this->clientAddress2 = $clientAddress2;

        return $this;
    }

    public function getClientAddress3(): ?string
    {
        return $this->clientAddress3;
    }

    public function setClientAddress3(?string $clientAddress3): LayDeputyshipDto
    {
        $this->clientAddress3 = $clientAddress3;

        return $this;
    }

    public function getClientAddress4(): ?string
    {
        return $this->clientAddress4;
    }

    public function setClientAddress4(?string $clientAddress4): LayDeputyshipDto
    {
        $this->clientAddress4 = $clientAddress4;

        return $this;
    }

    public function getClientAddress5(): ?string
    {
        return $this->clientAddress5;
    }

    public function setClientAddress5(?string $clientAddress5): LayDeputyshipDto
    {
        $this->clientAddress5 = $clientAddress5;

        return $this;
    }

    public function getClientPostcode(): ?string
    {
        return $this->clientPostcode;
    }

    public function setClientPostcode(?string $clientPostcode): LayDeputyshipDto
    {
        $this->clientPostcode = $clientPostcode;

        return $this;
    }
}
