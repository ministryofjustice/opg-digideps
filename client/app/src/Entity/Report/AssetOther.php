<?php

namespace OPG\Digideps\Frontend\Entity\Report;

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
    private string $description;

    /**
     * Set description
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return 'other';
    }

    public function getListTemplateName(): string
    {
        $titleToTemplateMap = [
            'Stocks and shares' => 'stock_share',
            'Premium bonds' => 'premium_bond',
            'Vehicles' => 'vehicle',
        ];

        return $titleToTemplateMap[$this->getTitle()] ?? 'default';
    }

    public function getBehatIdentifier(): string
    {
        return $this->getDescription();
    }
}
