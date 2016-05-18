<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AssetOther extends Asset
{
    /**
     * @Assert\NotBlank(message="asset.description.notBlank")
     * @Assert\Length(min=3, minMessage="asset.description.length")
     * 
     * @JMS\Type("string")
     */
    private $description;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \Date
     */
    private $valuationDate;

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Asset
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set valuationDate.
     *
     * @param \DateTime $valuationDate
     *
     * @return Asset
     */
    public function setValuationDate($valuationDate)
    {
        $this->valuationDate = $valuationDate;

        return $this;
    }

    /**
     * Get valuationDate.
     *
     * @return \DateTime
     */
    public function getValuationDate()
    {
        return $this->valuationDate;
    }

    public function getType()
    {
        return 'other';
    }
}
