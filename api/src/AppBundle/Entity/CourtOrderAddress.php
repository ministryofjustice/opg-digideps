<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="court_order_address")
 * @ORM\Entity()
 */
class CourtOrderAddress
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="court_order_address_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressLine1 = '';

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressLine2 = '';

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $addressLine3 = '';

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $town = '';

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $county = '';

    /**
     * @var string|null
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $postcode = '';

    /**
     * @var string|null
     * @Assert\Country
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $country = '';

    /**
     * @var CourtOrderDeputy
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CourtOrderDeputy", inversedBy="addresses", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="court_order_deputy_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $deputy;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function getAddressLine3(): ?string
    {
        return $this->addressLine3;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getDeputy(): CourtOrderDeputy
    {
        return $this->deputy;
    }

    public function setAddressLine1(?string $addressLine1): CourtOrderAddress
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function setAddressLine2(?string $addressLine2): CourtOrderAddress
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function setAddressLine3(?string $addressLine3): CourtOrderAddress
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    public function setTown(?string $town): CourtOrderAddress
    {
        $this->town = $town;

        return $this;
    }

    public function setCounty(?string $county): CourtOrderAddress
    {
        $this->county = $county;

        return $this;
    }

    public function setPostcode(?string $postcode): CourtOrderAddress
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function setCountry(?string $country): CourtOrderAddress
    {
        $this->country = $country;

        return $this;
    }

    public function setDeputy(CourtOrderDeputy $deputy): CourtOrderAddress
    {
        $this->deputy = $deputy;

        return $this;
    }
}
