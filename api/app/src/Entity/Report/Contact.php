<?php

namespace App\Entity\Report;

use App\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Contacts.
 *
 * @ORM\Table(name="contact")
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Contact
{
    use CreateUpdateTimestamps;

    /**
     * @var int
     *
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="contact_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['contact'])]
    private $id;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="contact_name", type="string", length=255, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $contactName;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="address", type="string", length=200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $address;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $address2;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="county", type="string", length=200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $county;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="postcode", type="string", length=10, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $postcode;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="country", type="string", length=10, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $country;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="explanation", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $explanation;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="relationship", type="string", length=100, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $relationship;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="phone1", type="string", length=20, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private $phone1;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="contacts")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set contact_name.
     *
     * @return Contact
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;

        return $this;
    }

    /**
     * Get contactName.
     *
     * @return string
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Set postcode.
     *
     * @param string $postcode
     *
     * @return Contact
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode.
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set explanation.
     *
     * @param string $explanation
     *
     * @return Contact
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * Get explanation.
     *
     * @return string
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Set relationship.
     *
     * @param string $relationship
     *
     * @return Contact
     */
    public function setRelationship($relationship)
    {
        $this->relationship = $relationship;

        return $this;
    }

    /**
     * Get relationship.
     *
     * @return string
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * Set phone1.
     *
     * @param string $phone1
     *
     * @return Contact
     */
    public function setPhone1($phone1)
    {
        $this->phone1 = $phone1;

        return $this;
    }

    /**
     * Get phone1.
     *
     * @return string
     */
    public function getPhone1()
    {
        return $this->phone1;
    }

    /**
     * Set report.
     *
     * @param Report $report
     *
     * @return Contact
     */
    public function setReport(Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report.
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return Contact
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address2.
     *
     * @param string $address2
     *
     * @return Contact
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2.
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set county.
     *
     * @param string $county
     *
     * @return Contact
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county.
     *
     * @return string
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return Contact
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
}
