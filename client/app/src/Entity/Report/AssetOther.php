<?php

namespace App\Entity\Report;

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

        return $titleToTemplateMap[$this->getTitle()] ?? 'default';
    }

    public function getBehatIdentifier()
    {
        return $this->getDescription();
    }
}
