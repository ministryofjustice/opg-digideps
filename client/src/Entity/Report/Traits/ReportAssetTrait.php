<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\AssetOther;
use App\Entity\Report\AssetProperty;
use App\Entity\Report\Report;
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
     * @JMS\Type("array<App\Entity\Report\Asset>")
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
     * @return array $assets e.g. [Property => [asset1, asset2], Bonds=>[]...]
     */
    public function getAssetsGroupedByTitle()
    {
        // those needs to be grouped together
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
            } elseif ($asset instanceof AssetOther) {
                $title = isset($titleToGroupOverride[$asset->getTitle()]) ?
                    $titleToGroupOverride[$asset->getTitle()] : $asset->getTitle();
            }

            // add asset into "items" and sum total
            $ret[$title]['items'][$asset->getId()] = $asset;
            $ret[$title]['total'] = isset($ret[$title]['total'])
                ? $ret[$title]['total'] + $asset->getValueTotal()
                : $asset->getValueTotal();
        }

        // order categories
        ksort($ret);
        // foreach category, order assets by ID desc
        foreach ($ret as &$row) {
            krsort($row['items']);
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
