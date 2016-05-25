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

    /**
     * @return mixed|string
     */
    public function getListTemplateName()
    {
        $titleToTemplateMap = [
            'Stocks and shares' => 'stock_share',
            'Premium bonds' => 'premium_bond',
            'Vehicles' => 'vehicle',
        ];

        return isset($titleToTemplateMap[$this->getTitle()])
            ? $titleToTemplateMap[$this->getTitle()] : 'default';
    }

    /**
     * @return string
     */
    public function getOrderIndex()
    {
        $ret = '';

        if ($this->getTitle() == 'Premium bonds') {
            if ($this->getValuationDate() instanceof \DateTime) {
                $ret = $this->getValuationDate()->format('Ymd');
            }
            $ret .= str_pad($this->getValue(), 30, '0', STR_PAD_LEFT);
        } else {
            $ret = strtolower($this->getDescription());
        }

        return $ret;
    }
}
