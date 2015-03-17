<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Asset
{
    /**
     *
     * @JMS\Type("integer")
     */
    private $id;
    
    /**
     * 
     * @Assert\NotBlank(message="asset.description.notBlank")
     * @Assert\Type( type="string", message="asset.description.type")
     * @Assert\Length(min=10, minMessage="asset.description.length")
     * 
     * @JMS\Type("string")
     */
    private $description;
    
    /**
     *
     * @Assert\NotBlank(message="asset.value.notBlank")
     * @Assert\Type( type="numeric", message="asset.value.type")
     * @JMS\Type("string")
     */
    private $value;
    
    /**
     *
     * @Assert\NotBlank(message="asset.title.notBlank")
     * @JMS\Type("string")
     */
    private $title;
    
    /**
     *
     * @Assert\NotBlank(message="asset.date.notBlank")
     * @Assert\Date(message="asset.date.date")
     * @JMS\Type("DateTime")
     */
    private $valuationDate;
    
    /**
     * @JMS\Type("integer")
     */
    private $report;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function setValuationDate($valuationDate)
    {
        $this->valuationDate = $valuationDate;
        return $this;
    }
    
    public function getValuationDate()
    {
        return $this->valuationDate;
    }
    
    public function setReport($report)
    {
        $this->report = $report;
        return $this;
    }
    
    public function getReport()
    {
        return $this->report;
    }
}