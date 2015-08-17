<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AssetController extends Controller
{
    
    /**
     * @Route("/report/{reportId}/assets/delete/{id}", name="delete_asset")
     * 
     * @param type $reportId
     * @param type $id
     */
    public function deleteAction($reportId,$id)
    {
        $util = $this->get('util');
        
         //just do some checks to make sure user is allowed to delete this contact
         $report = $util->getReport($reportId, $this->getUser()->getId(), ['transactions']);
         
        if(!empty($report) && in_array($id, $report->getAssets())){
            $this->get('apiclient')->delete('delete_report_asset', [ 'parameters' => [ 'id' => $id ]]);
        }
        return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId ]));
    }
    
    /**
     * --action [ list, add, edit, delete-confirm ]
     * @Route("/report/{reportId}/assets/{action}/{id}", name="assets", defaults={ "action" = "list", "id" = " "})
     * @Template()
     */
    public function assetsAction($reportId, $action,$id)
    {
        $util = $this->get('util');
        $translator =  $this->get('translator');
        $dropdownKeys = $this->container->getParameter('asset_dropdown');
        $apiClient = $this->get('apiclient');
        $request = $this->getRequest();
        
        $titles = [];
        
        foreach($dropdownKeys as $key ){
            $translation = $translator->trans($key,[],'report-assets');
            $titles[$translation] = $translation;
        }

        $other = $titles['Other assets'];
        unset($titles['Other assets']);
        
        asort($titles);
        $titles['Other assets'] = $other;
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());

        if(in_array($action, [ 'edit', 'delete-confirm'])){
            $asset = $apiClient->getEntity('Asset','get_report_asset', [ 'parameters' => ['id' => $id ]]);
            if (!in_array($id, $report->getAssets())) {
               throw new \RuntimeException("Asset not found.");
            }
            $form = $this->createForm(new FormDir\AssetType($titles),$asset, [ 'action' => $this->generateUrl('assets', [ 'reportId' => $reportId, 'action' => 'edit', 'id' => $asset->getId() ])]);
        }else{
            $asset = new EntityDir\Asset();
            $form = $this->createForm(new FormDir\AssetType($titles),$asset, [ 'action' => $this->generateUrl('assets', [ 'reportId' => $reportId, 'action' => 'add'])]);
        }
        
        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }

        $assets = $apiClient->getEntities('Asset','get_report_assets', [ 'parameters' => ['id' => $reportId ]]);

        $reportSubmit = $this->createForm(new FormDir\ReportSubmitType($this->get('translator')));
        $noAssetsToAdd = $this->createForm(new FormDir\NoAssetToAddType());
        
        if($request->getMethod() == 'POST'){
            
            $form->handleRequest($request);
            
            $reportSubmit->handleRequest($request);
            $noAssetsToAdd->handleRequest($request);
            
            if($form->get('save')->isClicked()){
                if($form->isValid()){
                    $asset = $form->getData();
                    
                    if($action == 'add'){
                        
                        $asset->setReport($reportId);
                        $apiClient->postC('add_report_asset', $asset);
                    
                        //lets clear no assets selected if the previously selected this
                        $report->setNoAssetToAdd(0);
                        $apiClient->putC('report/'.$report->getId(),$report);
                    
                    }else{
                        $apiClient->putC('update_report_asset', $asset);
                    }
                    return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId ]));
                }
            }elseif($noAssetsToAdd->get('saveNoAsset')->isClicked()){
                
                if($noAssetsToAdd->isValid()){
                    $report->setNoAssetToAdd(true);
                    $apiClient->putC('report/'.$report->getId(),$report);
                    return $this->redirect($this->generateUrl('assets',[ 'reportId' => $report->getId()]));
                }
            }else{

                if($reportSubmit->isValid()){
                    if($report->readyToSubmit()){
                        return $this->redirect($this->generateUrl('report_declaration', [ 'reportId' => $report->getId() ]));
                    }
                }
            }
        }

        return [
            'report' => $report,
            'client' => $client,
            'action' => $action,
            'form'   => $form->createView(),
            'no_assets_to_add' => $noAssetsToAdd->createView(),
            'assets' => $assets,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }
}