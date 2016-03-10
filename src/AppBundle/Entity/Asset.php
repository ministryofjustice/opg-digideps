<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

abstract class Asset
{
    use Traits\HasReportTrait;
    
    abstract public function getType();
    
     /**
     * @param string $type
     * @return Asset instance
     */
    public static function factory($type)
    {
        switch (strtolower($type)) {
            case 'property':
                return new AssetProperty();
            default:
                return new AssetOther();
        }
    }
    
    /**
     *
     * @JMS\Type("integer")
     */
    private $id;
    
    /**
     *
     * @Assert\NotBlank(message="asset.value.notBlank")
     * @Assert\Type( type="numeric", message="asset.value.type")
     * @Assert\Range(max=10000000000, maxMessage = "asset.value.outOfRange")
     * @JMS\Type("string")
     */
    private $value;
    
    /**
     * @Assert\Date(message="asset.date.date")
     * @JMS\Type("DateTime")
     */
    private $valuationDate;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
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
    
    public function setValuationDate($valuationDate)
    {
        $this->valuationDate = $valuationDate;
        return $this;
    }
    
    public function getValuationDate()
    {
        return $this->valuationDate;
    }
}