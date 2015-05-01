<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;

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
        $client = $util->getClient($report->getClient());

        if(in_array($action, [ 'edit', 'delete-confirm'])){
            $asset = $apiClient->getEntity('Asset','get_report_asset', [ 'parameters' => ['id' => $id ]]);
        }else{
            $asset = new EntityDir\Asset();
        }
       
        $form = $this->createForm(new FormDir\AssetType($titles),$asset);
        $reportSubmit = $this->createForm(new FormDir\ReportSubmitType($this->get('translator')));

        $assets = $apiClient->getEntities('Asset','get_report_assets', [ 'parameters' => ['id' => $reportId ]]);
        
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            $reportSubmit->handleRequest($request);

            if($form->get('save')->isClicked()){
                if($form->isValid()){
                    $asset = $form->getData();
                    
                    if($action == 'add'){
                        $asset->setReport($reportId);
                        $apiClient->postC('add_report_asset', $asset);
                    }else{
                        $apiClient->putC('update_report_asset', $asset);
                    }
                    return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId ]));
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
            'assets' => $assets,
            'report_form_submit' => $reportSubmit->createView()
        ];
    }
}