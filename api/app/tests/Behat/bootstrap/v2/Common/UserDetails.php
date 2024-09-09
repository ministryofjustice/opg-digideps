<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;

class UserDetails
{
    public const ADMIN_ROLES = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN_MANAGER'];

    private ?string $clientCaseNumber = null;
    private ?string $clientEmail = null;
    private ?string $clientFirstName = null;
    private ?array $clientFullAddressArray = null;
    private ?\DateTime $clientArchivedAt = null;
    private ?int $clientId = null;
    private ?string $clientLastName = null;
    private ?string $courtDate = null;
    private ?int $currentReportBankAccountId = null;
    private ?\DateTime $currentReportDueDate = null;
    private ?\DateTime $currentReportEndDate = null;
    private ?int $currentReportId = null;
    private ?string $currentReportNdrOrReport = null;
    private ?\DateTime $currentReportStartDate = null;
    private ?string $currentReportType = null;
    private ?string $deputyEmail = null;
    private ?string $deputyEmailAlt = null;
    private ?array $deputyFullAddressArray = null;
    private ?string $deputyName = null;
    private ?string $deputyPhone = null;
    private ?string $deputyPhoneAlt = null;
    private ?string $organisationEmailIdentifier = null;
    private ?string $organisationName = null;
    private ?int $previousReportBankAccountId = null;
    private ?\DateTime $previousReportDueDate = null;
    private ?\DateTime $previousReportEndDate = null;
    private ?int $previousReportId = null;
    private ?string $previousReportNdrOrReport = null;
    private ?\DateTime $previousReportStartDate = null;
    private ?string $previousReportType = null;
    private ?string $userEmail = null;
    private ?string $userFirstName = null;
    private ?string $userFullName = null;
    private ?array $userFullAddressArray = null;
    private ?string $userLastName = null;
    private ?int $userId = null;
    private ?string $userPhone = null;
    private ?string $userRole = null;

    public function __construct(array $userDetails)
    {
        $this->initialize($userDetails);
    }

    private function initialize(array $userDetails)
    {
        $supportedArrayKeys = $this->getProperties();

        $inputArrayKeys = array_keys($userDetails);
        $unexpectedKeys = [];

        foreach ($inputArrayKeys as $index => $key) {
            if (!in_array($key, $supportedArrayKeys)) {
                $unexpectedKeys[] = $key;
            }
        }

        if (!empty($unexpectedKeys)) {
            $unexpectedKeysList = implode(', ', $unexpectedKeys);
            $supportedKeysList = implode(', ', $supportedArrayKeys);

            throw new BehatException(sprintf('Unexpected keys encountered when trying to initialize UserDetails: %s. Supported keys are: %s', $unexpectedKeysList, $supportedKeysList));
        }

        $this->setUserId($userDetails['userId'] ?? null);
        $this->setUserEmail($userDetails['userEmail'] ?? null);
        $this->setUserRole($userDetails['userRole'] ?? null);
        $this->setUserFirstName($userDetails['userFirstName'] ?? null);
        $this->setUserLastName($userDetails['userLastName'] ?? null);
        $this->setUserFullName($userDetails['userFullName'] ?? null);
        $this->setUserFullAddressArray($userDetails['userFullAddressArray'] ?? null);
        $this->setUserPhone($userDetails['userPhone'] ?? null);

        $this->setDeputyName($userDetails['deputyName'] ?? null);
        $this->setDeputyFullAddressArray($userDetails['deputyFullAddressArray'] ?? null);
        $this->setDeputyPhone($userDetails['deputyPhone'] ?? null);
        $this->setDeputyPhoneAlt($userDetails['deputyPhoneAlt'] ?? null);
        $this->setDeputyEmail($userDetails['deputyEmail'] ?? null);
        $this->setDeputyEmailAlt($userDetails['deputyEmailAlt'] ?? null);
        $this->setOrganisationName($userDetails['organisationName'] ?? null);
        $this->setOrganisationEmailIdentifier($userDetails['organisationEmailIdentifier'] ?? null);
        $this->setCourtDate($userDetails['courtDate'] ?? null);

        $this->setClientId($userDetails['clientId'] ?? null);
        $this->setClientFirstName($userDetails['clientFirstName'] ?? null);
        $this->setClientLastName($userDetails['clientLastName'] ?? null);
        $this->setClientFullAddressArray($userDetails['clientFullAddressArray'] ?? null);
        $this->setClientEmail($userDetails['clientEmail'] ?? null);
        $this->setClientCaseNumber($userDetails['clientCaseNumber'] ?? null);
        $this->setClientArchivedAt($userDetails['clientArchivedAt'] ?? null);

        $currentReportId = $userDetails['currentReportId'] ?? null;
        $previousReportId = $userDetails['previousReportId'] ?? null;

        $this->setCurrentReportId($userDetails['currentReportId'] ?? null);
        $this->setCurrentReportType($userDetails['currentReportType'] ?? null);
        $this->setCurrentReportNdrOrReport($userDetails['currentReportNdrOrReport'] ?? null);
        $this->setCurrentReportDueDate($userDetails['currentReportDueDate'] ?? null);
        $this->setCurrentReportStartDate($userDetails['currentReportStartDate'] ?? null);
        $this->setCurrentReportEndDate($userDetails['currentReportEndDate'] ?? null);
        $this->setCurrentReportBankAccountId($userDetails['currentReportBankAccountId'] ?? null);

        if ($currentReportId !== $previousReportId) {
            $this->setPreviousReportId($previousReportId);
            $this->setPreviousReportType($userDetails['previousReportType'] ?? null);
            $this->setPreviousReportNdrOrReport($userDetails['previousReportNdrOrReport'] ?? null);
            $this->setPreviousReportDueDate($userDetails['previousReportDueDate'] ?? null);
            $this->setPreviousReportStartDate($userDetails['previousReportStartDate'] ?? null);
            $this->setPreviousReportEndDate($userDetails['previousReportEndDate'] ?? null);
            $this->setPreviousReportEndDate($userDetails['previousReportEndDate'] ?? null);
            $this->setPreviousReportBankAccountId($userDetails['previousReportBankAccountId'] ?? null);
        }
    }

    private function getProperties(): array
    {
        $reflect = new \ReflectionClass(self::class);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PRIVATE);
        $classProperties = [];

        foreach ($props as $prop) {
            $classProperties[] = $prop->getName();
        }

        return $classProperties;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(?string $email): UserDetails
    {
        $this->userEmail = $email;

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(?int $clientId): UserDetails
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getCurrentReportId(): ?int
    {
        return $this->currentReportId;
    }

    public function setCurrentReportId(?int $currentReportId): UserDetails
    {
        $this->currentReportId = $currentReportId;

        return $this;
    }

    public function getCurrentReportType(): ?string
    {
        return $this->currentReportType;
    }

    public function setCurrentReportType(?string $currentReportType): UserDetails
    {
        $this->currentReportType = $currentReportType;

        return $this;
    }

    public function getPreviousReportId(): ?int
    {
        return $this->previousReportId;
    }

    public function setPreviousReportId(?int $previousReportId): UserDetails
    {
        $this->previousReportId = $previousReportId;

        return $this;
    }

    public function getPreviousReportType(): ?string
    {
        return $this->previousReportType;
    }

    public function setPreviousReportType(?string $previousReportType): UserDetails
    {
        $this->previousReportType = $previousReportType;

        return $this;
    }

    public function getCurrentReportNdrOrReport(): ?string
    {
        return $this->currentReportNdrOrReport;
    }

    public function setCurrentReportNdrOrReport(?string $currentReportNdrOrReport): UserDetails
    {
        $this->currentReportNdrOrReport = $currentReportNdrOrReport;

        return $this;
    }

    public function getPreviousReportNdrOrReport(): ?string
    {
        return $this->previousReportNdrOrReport;
    }

    public function setPreviousReportNdrOrReport(?string $previousReportNdrOrReport): UserDetails
    {
        $this->previousReportNdrOrReport = $previousReportNdrOrReport;

        return $this;
    }

    public function getClientFirstName(): ?string
    {
        return $this->clientFirstName;
    }

    public function setClientFirstName(?string $clientFirstName): UserDetails
    {
        $this->clientFirstName = $clientFirstName;

        return $this;
    }

    public function getClientLastName(): ?string
    {
        return $this->clientLastName;
    }

    public function setClientLastName(?string $clientLastName): UserDetails
    {
        $this->clientLastName = $clientLastName;

        return $this;
    }

    public function getClientFullAddressArray(): ?array
    {
        return $this->clientFullAddressArray;
    }

    public function setClientFullAddressArray(?array $clientFullAddressArray): UserDetails
    {
        $this->clientFullAddressArray = $clientFullAddressArray;

        return $this;
    }

    public function getClientCaseNumber(): ?string
    {
        return $this->clientCaseNumber;
    }

    public function setClientCaseNumber(?string $clientCaseNumber): UserDetails
    {
        $this->clientCaseNumber = $clientCaseNumber;

        return $this;
    }

    public function getClientArchivedAt(): ?\DateTime
    {
        return $this->clientArchivedAt;
    }

    public function setClientArchivedAt(?\DateTime $clientArchivedAt): UserDetails
    {
        $this->clientArchivedAt = $clientArchivedAt;

        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(?string $clientEmail): UserDetails
    {
        $this->clientEmail = $clientEmail;

        return $this;
    }

    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    public function setUserRole(?string $userRole): UserDetails
    {
        $this->userRole = $userRole;

        return $this;
    }

    public function getUserFirstName(): ?string
    {
        return $this->userFirstName;
    }

    public function setUserFirstName(?string $userFirstName): UserDetails
    {
        $this->userFirstName = $userFirstName;

        return $this;
    }

    public function getUserLastName(): ?string
    {
        return $this->userLastName;
    }

    public function setUserLastName(?string $userLastName): UserDetails
    {
        $this->userLastName = $userLastName;

        return $this;
    }

    public function getUserFullName(): ?string
    {
        return $this->userFullName;
    }

    public function setUserFullName(?string $userFullName): UserDetails
    {
        $this->userFullName = $userFullName;

        return $this;
    }

    public function getUserFullAddressArray(): ?array
    {
        return $this->userFullAddressArray;
    }

    public function setUserFullAddressArray(?array $userFullAddressArray): UserDetails
    {
        $this->userFullAddressArray = $userFullAddressArray;

        return $this;
    }

    public function getUserPhone(): ?string
    {
        return $this->userPhone;
    }

    public function setUserPhone(?string $userPhone): UserDetails
    {
        $this->userPhone = $userPhone;

        return $this;
    }

    public function getCurrentReportDueDate(): ?\DateTime
    {
        return $this->currentReportDueDate;
    }

    public function setCurrentReportDueDate(?\DateTime $currentReportDueDate): UserDetails
    {
        $this->currentReportDueDate = $currentReportDueDate;

        return $this;
    }

    public function getPreviousReportDueDate(): ?\DateTime
    {
        return $this->previousReportDueDate;
    }

    public function setPreviousReportDueDate(?\DateTime $previousReportDueDate): UserDetails
    {
        $this->previousReportDueDate = $previousReportDueDate;

        return $this;
    }

    public function getOrganisationName(): ?string
    {
        return $this->organisationName;
    }

    public function setOrganisationName(?string $organisationName): UserDetails
    {
        $this->organisationName = $organisationName;

        return $this;
    }

    public function getDeputyName(): ?string
    {
        return $this->deputyName;
    }

    public function setDeputyName(?string $deputyName): UserDetails
    {
        $this->deputyName = $deputyName;

        return $this;
    }

    public function getDeputyFullAddressArray(): ?array
    {
        return $this->deputyFullAddressArray;
    }

    public function setDeputyFullAddressArray(?array $deputyFullAddressArray): UserDetails
    {
        $this->deputyFullAddressArray = $deputyFullAddressArray;

        return $this;
    }

    public function getDeputyPhone(): ?string
    {
        return $this->deputyPhone;
    }

    public function setDeputyPhone(?string $deputyPhone): UserDetails
    {
        $this->deputyPhone = $deputyPhone;

        return $this;
    }

    public function getDeputyPhoneAlt(): ?string
    {
        return $this->deputyPhoneAlt;
    }

    public function setDeputyPhoneAlt(?string $deputyPhoneAlt): UserDetails
    {
        $this->deputyPhoneAlt = $deputyPhoneAlt;

        return $this;
    }

    public function getDeputyEmail(): ?string
    {
        return $this->deputyEmail;
    }

    public function setDeputyEmail(?string $deputyEmail): UserDetails
    {
        $this->deputyEmail = $deputyEmail;

        return $this;
    }

    public function getDeputyEmailAlt(): ?string
    {
        return $this->deputyEmailAlt;
    }

    public function setDeputyEmailAlt(?string $deputyEmailAlt): UserDetails
    {
        $this->deputyEmailAlt = $deputyEmailAlt;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): UserDetails
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCourtDate(): ?string
    {
        return $this->courtDate;
    }

    public function setCourtDate(?string $courtDate): UserDetails
    {
        $this->courtDate = $courtDate;

        return $this;
    }

    public function getOrganisationEmailIdentifier(): ?string
    {
        return $this->organisationEmailIdentifier;
    }

    public function setOrganisationEmailIdentifier(?string $organisationEmailIdentifier): UserDetails
    {
        $this->organisationEmailIdentifier = $organisationEmailIdentifier;

        return $this;
    }

    public function getCurrentReportStartDate(): ?\DateTime
    {
        return $this->currentReportStartDate;
    }

    public function setCurrentReportStartDate(?\DateTime $currentReportStartDate): UserDetails
    {
        $this->currentReportStartDate = $currentReportStartDate;

        return $this;
    }

    public function getCurrentReportEndDate(): ?\DateTime
    {
        return $this->currentReportEndDate;
    }

    public function setCurrentReportEndDate(?\DateTime $currentReportEndDate): UserDetails
    {
        $this->currentReportEndDate = $currentReportEndDate;

        return $this;
    }

    public function getPreviousReportStartDate(): ?\DateTime
    {
        return $this->previousReportStartDate;
    }

    public function setPreviousReportStartDate(?\DateTime $previousReportStartDate): UserDetails
    {
        $this->previousReportStartDate = $previousReportStartDate;

        return $this;
    }

    public function getPreviousReportEndDate(): ?\DateTime
    {
        return $this->previousReportEndDate;
    }

    public function setPreviousReportEndDate(?\DateTime $previousReportEndDate): UserDetails
    {
        $this->previousReportEndDate = $previousReportEndDate;

        return $this;
    }

    public function getCurrentReportPeriod(): ?string
    {
        return sprintf(
            '%s-%s',
            $this->getCurrentReportStartDate()->format('Y'),
            $this->getCurrentReportEndDate()->format('Y'),
        );
    }

    public function getPreviousReportPeriod(): ?string
    {
        return sprintf(
            '%s-%s',
            $this->getPreviousReportStartDate()->format('Y'),
            $this->getPreviousReportEndDate()->format('Y'),
        );
    }

    public function getCurrentReportBankAccountId(): ?int
    {
        return $this->currentReportBankAccountId;
    }

    public function setCurrentReportBankAccountId(?int $currentReportBankAccountId): UserDetails
    {
        $this->currentReportBankAccountId = $currentReportBankAccountId;

        return $this;
    }

    public function getPreviousReportBankAccountId(): ?int
    {
        return $this->previousReportBankAccountId;
    }

    public function setPreviousReportBankAccountId(?int $previousReportBankAccountId): UserDetails
    {
        $this->previousReportBankAccountId = $previousReportBankAccountId;

        return $this;
    }
}
