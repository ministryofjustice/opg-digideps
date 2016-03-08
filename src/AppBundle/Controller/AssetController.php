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
                'report_id' => 'mustExist'
            ]);
            $report = $this->findEntityBy('Report', $assetData['report_id']);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $asset = EntityDir\Asset::factory($assetData['type']);
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
        
        if ($asset instanceof EntityDir\AssetOther) {
            $this->hydrateEntityWithArrayData($asset, $assetData, [
                'description' => 'setDescription',
                'value' => 'setValue',
                'title' => 'setTitle',
            ]);

            if (!empty($assetData['valuation_date'])) {
                $valuationDate = new \DateTime($assetData['valuation_date']);
            } else {
                $valuationDate = null;
            }
            $asset->setValuationDate($valuationDate);
        }
        
        if ($asset instanceof EntityDir\AssetProperty) {

            $this->hydrateEntityWithArrayData($asset, $assetData, [
                'occupants' => 'setOccupants',
                'occupantsInfo' => 'setOccupantsInfo',
                'owned' => 'setOwned',
                'owned_percentage' => 'setOwnedPercentage',
                'is_subject_to_equity_release' => 'setIsSubjectToEquityRelease',
                'has_mortgage' => 'setHasMortgage',
                'mortgage_outstanding_amount' => 'setMortgageOutstandingAmount',
                'has_charges' => 'setHasCharges',
                'is_rented_out' => 'setIsRentedOut',
                'rent_income_month' => 'setRentIncomeMonth',
                'address' => 'setAddress',
                'address2' => 'setAddress2',
                'county' => 'setCounty',
                'postcode' => 'setPostCode',
                'value' => 'setValue',
            ]);

            if (isset($assetData['rent_agreement_end_date'])) {
                $asset->setRentAgreementEndDate(new \DateTime($assetData['rentAgreementEndDate']['date']));
            }
        }


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