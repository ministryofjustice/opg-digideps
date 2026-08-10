<?php

namespace OPG\Digideps\Frontend\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class PreRegistration
{
    public const string REALM_PA = 'REALM_PA';
    public const string REALM_PROF = 'REALM_PROF';
    public const string REALM_LAY = 'REALM_LAY';

    private int $id;

    #[JMS\Type('string')]
    #[Assert\NotBlank]
    private string $caseNumber;

    #[JMS\Type('string')]
    #[Assert\NotBlank]
    private string $clientLastname;

    #[JMS\Type('string')]
    #[Assert\NotBlank]
    private string $deputyUid;

    #[JMS\Type('string')]
    #[Assert\NotBlank]
    private string $deputySurname;

    #[JMS\Type('string')]
    #[Assert\Length(min: 2, max: 20, minMessage: 'postcode too short', maxMessage: 'postcode too long')]
    private string $deputyPostCode;

    #[JMS\Type('string')]
    private string $typeOfReport;

    #[JMS\Type('string')]
    private string $orderType;

    #[JMS\Type('string')]
    private string $otherColumns;

    private \DateTime $orderDate;

    public function __construct()
    {
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    public function getClientLastname(): string
    {
        return $this->clientLastname;
    }

    public function getDeputyUid(): string
    {
        return $this->deputyUid;
    }

    public function getDeputySurname(): string
    {
        return $this->deputySurname;
    }

    public function getDeputyPostCode(): string
    {
        return $this->deputyPostCode;
    }

    public function getTypeOfReport(): string
    {
        return $this->typeOfReport;
    }

    public function getOrderType(): string
    {
        return $this->orderType;
    }

    public function getOrderDate(): \DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTime $orderDate): static
    {
        $this->orderDate = $orderDate;

        return $this;
    }
}
