<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;

/**
 * @Route("/report")
 */
class AssetController extends RestController
{
    /**
     * @Route("/{id}/assets")
     * @Method({"GET"})
     */
    public function getAssets($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $assets = $this->getRepository('Asset')->findByReport($report);

        if (count($assets) == 0) {
            return [];
        }
        return $assets;
    }
    
    
    
    /**
     * @Route("/asset/{id}")
     * @Method({"DELETE"})
     * 
     * @param integer $id
     */
    public function deleteAsset($id)
    { 
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $asset = $this->findEntityBy('Asset', $id, 'Asset not found');
        $report = $asset->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        // reset asset choice
        $report->setNoAssetToAdd(null);
        
        $this->getEntityManager()->remove($asset);
        $this->getEntityManager()->flush();
        
        return [ ];
    }
    
    
     /**
     * @Route("/asset/{id}")
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function getOneById($id)
    { 
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $asset = $this->findEntityBy('Asset', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());
        
        return $asset;
    }
    
    /**
     * @Route("/asset")
     * @Method({"POST", "PUT"})
     */
    public function upsertAsset(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $assetData = $this->deserializeBodyContent($request);
        
        if ($request->getMethod() == 'POST') {
            $this->validateArray($assetData, [
                'report' => 'mustExist'
            ]);
            $report = $this->findEntityBy('Report', $assetData['report']);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $asset = new EntityDir\Asset();
            $asset->setReport($report);
        } else {
            $this->validateArray($assetData, [
                'id' => 'mustExist'
            ]);
            $asset = $this->findEntityBy('Asset', $assetData['id']);
            $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());
        }
        
        $this->validateArray($assetData, [
            'description' => 'mustExist', 
            'value' => 'mustExist', 
            'title' => 'mustExist', 
        ]);
        
        $asset->setDescription($assetData['description']);
        $asset->setValue($assetData['value']);
        $asset->setTitle($assetData['title']);
        
        if(!empty($assetData['valuation_date'])){
            $valuationDate = new \DateTime($assetData['valuation_date']);
        }else{
            $valuationDate = null;
        }
        
        $asset->setValuationDate($valuationDate);
        $asset->setLastedit(new \DateTime());
        
        $this->persistAndFlush($asset);
        
        return [ 'id' => $asset->getId() ];
    }
}