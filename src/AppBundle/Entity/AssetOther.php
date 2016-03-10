<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AssetOther extends Asset
{
     /**
     * @Assert\NotBlank(message="asset.title.notBlank", groups={"title_only"})
     * @Assert\Length(max=100, maxMessage= "asset.title.maxMessage", groups={"title_only"})
     * @JMS\Type("string")
     */
    private $title;

    /**
     * 
     * @Assert\NotBlank(message="asset.description.notBlank")
     * @Assert\Length(min=3, minMessage="asset.description.length")
     * 
     * @JMS\Type("string")
     */
    private $description;

    /**
     * @var \Date
     */
    private $valuationDate;

    /**
     * @JMS\Type("string")
     * @var string
     */
    private $type = 'other';
    
    /**
     * Set description
     *
     * @param string $description
     * @return Asset
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set valuationDate
     *
     * @param \DateTime $valuationDate
     * @return Asset
     */
    public function setValuationDate($valuationDate)
    {
        $this->valuationDate = $valuationDate;

        return $this;
    }

    /**
     * Get valuationDate
     *
     * @return \DateTime 
     */
    public function getValuationDate()
    {
        return $this->valuationDate;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Asset
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    public function getType()
    {
        return $this->type;
    }
}
