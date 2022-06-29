<?php

namespace App\Entity;

use DateTime;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class PreRegistration
{
    const REALM_PA = 'REALM_PA';
    const REALM_PROF = 'REALM_PROF';
    const REALM_LAY = 'REALM_LAY';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $caseNumber;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $clientLastname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $deputyUid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $deputySurname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\Length(min=2, max=10, minMessage="postcode too short", maxMessage="postcode too long" )
     */
    private $deputyPostCode;

    /**
     * @JMS\Type("string")
     */
    private $typeOfReport;

    /**
     * @JMS\Type("string")
     */
    private $orderType;

    /**
     * @JMS\Type("string")
     */
    private $otherColumns;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $updatedAt;

    /**
     * @var DateTime
     */
    private $orderDate;

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

    public function getOrderDate(): DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(DateTime $orderDate): PreRegistration
    {
        $this->orderDate = $orderDate;

        return $this;
    }
}
