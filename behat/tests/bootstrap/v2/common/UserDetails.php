<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

use Exception;
use ReflectionClass;
use ReflectionProperty;

class UserDetails
{
    public const ADMIN_ROLES = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ELEVATED_ADMIN'];

    private ?string $userEmail = null;
    private ?string $userRole = null;
    private ?string $userFirstName = null;
    private ?string $userLastName = null;
    private ?string $userFullName = null;
    private ?array $userFullAddressArray = null;
    private ?string $userPhone = null;
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

            throw new Exception(
                sprintf(
                    'Unexpected keys encountered when trying to initialize UserDetails: %s. Supported keys are: %s',
                    $unexpectedKeysList,
                    $supportedKeysList
                )
            );
        }

        $this->setUserEmail($userDetails['userEmail']);
        $this->setUserRole($userDetails['userRole']);
        $this->setUserFirstName($userDetails['userFirstName']);
        $this->setUserLastName($userDetails['userLastName']);
        $this->setUserFullName($userDetails['userFullName']);
        $this->setUserFullAddressArray($userDetails['userFullAddressArray']);
        $this->setUserPhone($userDetails['userPhone']);
        $this->setUserEmail($userDetails['userEmail']);

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

    /**
     * @return null|string
     */
    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    /**
     * @param null|string $email
     * @return UserDetails
     */
    public function setUserEmail(?string $email): UserDetails
    {
        $this->userEmail = $email;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    /**
     * @param int|null $clientId
     * @return UserDetails
     */
    public function setClientId(?int $clientId): UserDetails
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCurrentReportId(): ?int
    {
        return $this->currentReportId;
    }

    /**
     * @param int|null $currentReportId
     * @return UserDetails
     */
    public function setCurrentReportId(?int $currentReportId): UserDetails
    {
        $this->currentReportId = $currentReportId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrentReportType(): ?string
    {
        return $this->currentReportType;
    }

    /**
     * @param string|null $currentReportType
     * @return UserDetails
     */
    public function setCurrentReportType(?string $currentReportType): UserDetails
    {
        $this->currentReportType = $currentReportType;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPreviousReportId(): ?int
    {
        return $this->previousReportId;
    }

    /**
     * @param int|null $previousReportId
     * @return UserDetails
     */
    public function setPreviousReportId(?int $previousReportId): UserDetails
    {
        $this->previousReportId = $previousReportId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreviousReportType(): ?string
    {
        return $this->previousReportType;
    }

    /**
     * @param string|null $previousReportType
     * @return UserDetails
     */
    public function setPreviousReportType(?string $previousReportType): UserDetails
    {
        $this->previousReportType = $previousReportType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrentReportNdrOrReport(): ?string
    {
        return $this->currentReportNdrOrReport;
    }

    /**
     * @param string|null $currentReportNdrOrReport
     * @return UserDetails
     */
    public function setCurrentReportNdrOrReport(?string $currentReportNdrOrReport): UserDetails
    {
        $this->currentReportNdrOrReport = $currentReportNdrOrReport;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreviousReportNdrOrReport(): ?string
    {
        return $this->previousReportNdrOrReport;
    }

    /**
     * @param string|null $previousReportNdrOrReport
     * @return UserDetails
     */
    public function setPreviousReportNdrOrReport(?string $previousReportNdrOrReport): UserDetails
    {
        $this->previousReportNdrOrReport = $previousReportNdrOrReport;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientFirstName(): ?string
    {
        return $this->clientFirstName;
    }

    /**
     * @param string|null $clientFirstName
     * @return UserDetails
     */
    public function setClientFirstName(?string $clientFirstName): UserDetails
    {
        $this->clientFirstName = $clientFirstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientLastName(): ?string
    {
        return $this->clientLastName;
    }

    /**
     * @param string|null $clientLastName
     * @return UserDetails
     */
    public function setClientLastName(?string $clientLastName): UserDetails
    {
        $this->clientLastName = $clientLastName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    /**
     * @param string|null $userRole
     * @return UserDetails
     */
    public function setUserRole(?string $userRole): UserDetails
    {
        $this->userRole = $userRole;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientCaseNumber(): ?string
    {
        return $this->clientCaseNumber;
    }

    /**
     * @param string|null $clientCaseNumber
     * @return UserDetails
     */
    public function setClientCaseNumber(?string $clientCaseNumber): UserDetails
    {
        $this->clientCaseNumber = $clientCaseNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCourtOrderNumber(): ?string
    {
        return $this->courtOrderNumber;
    }

    /**
     * @param string|null $courtOrderNumber
     * @return UserDetails
     */
    public function setCourtOrderNumber(?string $courtOrderNumber): UserDetails
    {
        $this->courtOrderNumber = $courtOrderNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserFirstName(): ?string
    {
        return $this->userFirstName;
    }

    /**
     * @param string|null $userFirstName
     * @return UserDetails
     */
    public function setUserFirstName(?string $userFirstName): UserDetails
    {
        $this->userFirstName = $userFirstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserLastName(): ?string
    {
        return $this->userLastName;
    }

    /**
     * @param string|null $userLastName
     * @return UserDetails
     */
    public function setUserLastName(?string $userLastName): UserDetails
    {
        $this->userLastName = $userLastName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserFullName(): ?string
    {
        return $this->userFullName;
    }

    /**
     * @param string|null $userFullName
     * @return UserDetails
     */
    public function setUserFullName(?string $userFullName): UserDetails
    {
        $this->userFullName = $userFullName;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getUserFullAddressArray(): ?array
    {
        return $this->userFullAddressArray;
    }

    /**
     * @param array|null $userFullAddressArray
     * @return UserDetails
     */
    public function setUserFullAddressArray(?array $userFullAddressArray): UserDetails
    {
        $this->userFullAddressArray = $userFullAddressArray;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserPhone(): ?string
    {
        return $this->userPhone;
    }

    /**
     * @param string|null $userPhone
     * @return UserDetails
     */
    public function setUserPhone(?string $userPhone): UserDetails
    {
        $this->userPhone = $userPhone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrentReportDueDate(): ?string
    {
        return $this->currentReportDueDate;
    }

    /**
     * @param string|null $currentReportDueDate
     * @return UserDetails
     */
    public function setCurrentReportDueDate(?string $currentReportDueDate): UserDetails
    {
        $this->currentReportDueDate = $currentReportDueDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreviousReportDueDate(): ?string
    {
        return $this->previousReportDueDate;
    }

    /**
     * @param string|null $previousReportDueDate
     * @return UserDetails
     */
    public function setPreviousReportDueDate(?string $previousReportDueDate): UserDetails
    {
        $this->previousReportDueDate = $previousReportDueDate;
        return $this;
    }
}
