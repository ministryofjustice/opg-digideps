<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

use Exception;
use ReflectionClass;
use ReflectionProperty;

class UserDetails
{
    private ?string $email;
    private ?int $clientId;
    private ?int $currentReportId;
    private ?string $currentReportType;
    private ?int $previousReportId;
    private ?string $previousReportType;

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

        $this->setEmail($userDetails['email']);
        $this->setClientId($userDetails['clientId']);
        $this->setCurrentReportId($userDetails['currentReportId']);
        $this->setCurrentReportType($userDetails['currentReportType']);
        $this->setPreviousReportId($userDetails['previousReportId']);
        $this->setPreviousReportType($userDetails['previousReportType']);
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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     * @return UserDetails
     */
    public function setEmail(?string $email): UserDetails
    {
        $this->email = $email;
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
}
