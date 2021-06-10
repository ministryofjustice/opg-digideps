<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use Exception;
use ReflectionClass;
use ReflectionProperty;

class UserDetails
{
    public const ADMIN_ROLES = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN_MANAGER'];

    private ?int $userId = null;
    private ?string $userEmail = null;
    private ?string $userRole = null;
    private ?string $userFirstName = null;
    private ?string $userLastName = null;
    private ?string $userFullName = null;
    private ?array $userFullAddressArray = null;
    private ?string $userPhone = null;
    private ?string $namedDeputyName = null;
    private ?string $namedDeputyEmail = null;
    private ?string $organisationName = null;
    private ?string $courtOrderNumber = null;
    private ?int $clientId = null;
    private ?string $clientFirstName = null;
    private ?string $clientLastName = null;
    private ?string $clientCaseNumber = null;
    private ?int $currentReportId = null;
    private ?string $currentReportType = null;
    private ?string $currentReportNdrOrReport = null;
    private ?string $currentReportDueDate = null;
    private ?int $previousReportId = null;
    private ?string $previousReportType = null;
    private ?string $previousReportNdrOrReport = null;
    private ?string $previousReportDueDate = null;

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

            throw new Exception(sprintf('Unexpected keys encountered when trying to initialize UserDetails: %s. Supported keys are: %s', $unexpectedKeysList, $supportedKeysList));
        }

        $this->setUserId($userDetails['userId']);
        $this->setUserEmail($userDetails['userEmail']);
        $this->setUserRole($userDetails['userRole']);
        $this->setUserFirstName($userDetails['userFirstName']);
        $this->setUserLastName($userDetails['userLastName']);
        $this->setUserFullName($userDetails['userFullName']);
        $this->setUserFullAddressArray($userDetails['userFullAddressArray']);
        $this->setUserPhone($userDetails['userPhone']);
        $this->setUserEmail($userDetails['userEmail']);

        $this->setNamedDeputyName($userDetails['namedDeputyName']);
        $this->setNamedDeputyEmail($userDetails['namedDeputyEmail']);
        $this->setOrganisationName($userDetails['organisationName']);
        $this->setCourtOrderNumber($userDetails['courtOrderNumber']);

        $this->setClientId($userDetails['clientId']);
        $this->setClientFirstName($userDetails['clientFirstName']);
        $this->setClientLastName($userDetails['clientLastName']);
        $this->setClientCaseNumber($userDetails['clientCaseNumber']);

        $this->setCurrentReportId($userDetails['currentReportId']);
        $this->setCurrentReportType($userDetails['currentReportType']);
        $this->setCurrentReportNdrOrReport($userDetails['currentReportNdrOrReport']);
        $this->setCurrentReportDueDate($userDetails['currentReportDueDate']);

        if ($userDetails['currentReportId'] !== $userDetails['previousReportId']) {
            $this->setPreviousReportId($userDetails['previousReportId']);
            $this->setPreviousReportType($userDetails['previousReportType']);
            $this->setPreviousReportNdrOrReport($userDetails['previousReportNdrOrReport']);
            $this->setPreviousReportDueDate($userDetails['previousReportDueDate']);
        }
    }

    private function getProperties(): array
    {
        $reflect = new ReflectionClass(self::class);
        $props = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);
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

    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    public function setUserRole(?string $userRole): UserDetails
    {
        $this->userRole = $userRole;

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

    public function getCourtOrderNumber(): ?string
    {
        return $this->courtOrderNumber;
    }

    public function setCourtOrderNumber(?string $courtOrderNumber): UserDetails
    {
        $this->courtOrderNumber = $courtOrderNumber;

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

    public function getCurrentReportDueDate(): ?string
    {
        return $this->currentReportDueDate;
    }

    public function setCurrentReportDueDate(?string $currentReportDueDate): UserDetails
    {
        $this->currentReportDueDate = $currentReportDueDate;

        return $this;
    }

    public function getPreviousReportDueDate(): ?string
    {
        return $this->previousReportDueDate;
    }

    public function setPreviousReportDueDate(?string $previousReportDueDate): UserDetails
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

    public function getNamedDeputyName(): ?string
    {
        return $this->namedDeputyName;
    }

    public function setNamedDeputyName(?string $namedDeputyName): UserDetails
    {
        $this->namedDeputyName = $namedDeputyName;

        return $this;
    }

    public function getNamedDeputyEmail(): ?string
    {
        return $this->namedDeputyEmail;
    }

    public function setNamedDeputyEmail(?string $namedDeputyEmail): UserDetails
    {
        $this->namedDeputyEmail = $namedDeputyEmail;

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
}
