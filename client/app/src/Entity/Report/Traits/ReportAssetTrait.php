<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\Asset;
use OPG\Digideps\Frontend\Entity\Report\AssetProperty;
use OPG\Digideps\Frontend\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

trait ReportAssetTrait
{
    /**
     * Titles matching this will be included in the count for "Cash" in summary page
     * Note: it relies on the translation (see report-assets.en.yml form.choices) for historical reasons
     *
     * @JMS\Exclude
     */
    private static $cashAssetTitles = [
        'Unit trusts',
        'National Savings certificates',
        'Stocks and shares',
        'Premium Bonds',
    ];


    /**
     * @JMS\Type("array<OPG\Digideps\Frontend\Entity\Report\Asset>")
     *
     * @var Asset[]
     */
    private $assets = [];

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $assetsTotalValue;

    /**
     * @param array $assets
     *
     * @return Report
     */
    public function setAssets($assets)
    {
        $this->assets = $assets;

        return $this;
    }

    /**
     * @return Asset[]
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Get assets total value.
     *
     * @return float
     */
    public function getAssetsTotalValue()
    {
        return $this->assetsTotalValue;
    }

    /**
     * @param string $type property|cash|other
     */
    public function getAssetsTotalsSummaryPage($type)
    {
        $ret = 0;

        foreach ($this->assets as $asset) {
            $isProperty = $asset instanceof AssetProperty;
            $isCash = in_array($asset->getTitle(), self::$cashAssetTitles);
            $isOther = !$isProperty && !$isCash;

            if (
                ($type === 'property' && $isProperty)
                || ($type === 'cash' && $isCash)
                || ($type === 'other' && $isOther)
            ) {
                $ret += $asset->getValueTotal();
            }
        }

        return $ret;
    }

    /**
     * Used in the list view
     * AssetProperty is considered having title "Property"
     * Artwork, Antiques, Jewellery are grouped into "Artwork, antiques and jewellery".
     *
     * @return array $assets e.g. ['Property' => ['items' => [asset1, asset2], 'total' => 422], 'Bonds' => [...], ...]
     */
    public function getAssetsGroupedByTitle(): array
    {
        // those should be grouped together
        $titleToGroupOverride = [
            'Artwork' => 'Artwork, antiques and jewellery',
            'Antiques' => 'Artwork, antiques and jewellery',
            'Jewellery' => 'Artwork, antiques and jewellery',
        ];

        $ret = [];

        foreach ($this->assets as $asset) {
            // select title
            if ($asset instanceof AssetProperty) {
                $title = 'Property';
            } else {
                $assetTitle = $asset->getTitle() ?? '';
                $title = $titleToGroupOverride[$assetTitle] ?? $assetTitle;
            }

            // add asset into "items" and sum total
            if (!array_key_exists($title, $ret)) {
                $ret[$title] = ['items' => [], 'total' => 0];
            }

            $ret[$title]['items'][] = $asset;
            $ret[$title]['total'] = $ret[$title]['total'] + ($asset->getValueTotal() ?? 0);
        }

        // order categories
        ksort($ret);

        // for each category, order assets by createdAt ascending
        foreach ($ret as $title => $row) {
            /** @var array<int, Asset> $assets */
            $assets = $row['items'];

            uasort($assets, fn ($asset1, $asset2) => $asset1->getCreatedAt() <=> $asset2->getCreatedAt());
            $ret[$title]['items'] = $assets;
        }

        return $ret;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasAssetWithId($id)
    {
        foreach ($this->getAssets() as $asset) {
            if ($asset->getId() == $id) {
                return true;
            }
        }

        return false;
    }
}
