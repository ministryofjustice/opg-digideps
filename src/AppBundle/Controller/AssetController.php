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
    public function getAssetsAction($id)
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
    public function deleteAssetAction($id)
    { 
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $asset = $this->findEntityBy('Asset', $id, 'Asset not found');
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());
        
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
    public function getOneAsse($id)
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
        
        $report = $this->findEntityBy('Report', $assetData['report']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        if($request->getMethod() == 'POST'){
            $asset = new EntityDir\Asset();
            $asset->setReport($report);
        }else{
            $asset = $this->findEntityBy('Asset', $assetData['id']);
            
            if(empty($asset)){
                throw new AppExceptions\NotFound("Asset with id:".$assetData['id'].' was not found', 404);
            }
        }
        
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