<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AssetOther extends Asset
{

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \Date
     */
    private $valuationDate;

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

}
