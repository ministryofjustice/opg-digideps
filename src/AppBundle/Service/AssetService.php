<?php 
namespace AppBundle\Service;

class AssetService
{

    /**
     * @return array $assetGroups
     */
    public function groupAssetTypes($assets)
    {
        $assetGroups = array();
        
        foreach ($assets as $asset) {
        
            $type = $data['title'];
        
            if (isset($assetGroups[$type])) {
                $assetGroups[$type][] = $asset;
            } else {
                $assetGroups[$type] = array($asset);
            }
        }
    
        return $assetGroups;
    }

}