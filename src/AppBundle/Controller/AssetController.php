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
     * @Route("/report/{reportId}/assets/add-select-title", name="asset_add_select_title")
     * @Template()
     */
    public function addSelectTitleAction($reportId)
    {
        $util = $this->get('util');
        $request = $this->getRequest();
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());
        
        $form = $this->createForm('asset_title', new EntityDir\Asset, [
            'action' => $this->generateUrl('asset_add_select_title', [ 'reportId' => $reportId])
        ]);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->redirect($this->generateUrl('asset_add_complete', [ 'reportId' => $reportId, 'title'=>$form->getData()->getTitle()]));
        }


        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
            'showCancelLink' => count($report->getAssets()) > 0
        ];
    }
    
    /**
     * @Route("/report/{reportId}/assets/add-complete/{title}", name="asset_add_complete")
     * @Template()
     */
    public function addCompleteAction($reportId, $title)
    {
        $util = $this->get('util');
        $apiClient = $this->get('apiclient');
        $request = $this->getRequest();
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());
        
        $asset = new EntityDir\Asset();
        $asset->setTitle($title);
        $form = $this->createForm(new FormDir\AssetType(), $asset);

        $form->handleRequest($request);

        // handle submit report
        if ($form->isValid()) {
            $asset = $form->getData();
            $asset->setReport($reportId);
            $apiClient->postC('add_report_asset', $asset);

            //lets clear no assets selected if the previously selected this
            $report->setNoAssetToAdd(0);
            $apiClient->putC('report/' . $report->getId(), $report);

            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }
    
    /**
     * Edit a record
     * Still uses the list view as the edit form is displayed "inline" along with the other records
     * 
     * @Route("/report/{reportId}/assets/{assetId}/edit", name="asset_edit")
     * @Template("AppBundle:Asset:list.html.twig")
     */
    public function editAction($reportId, $assetId)
    {
        $util = $this->get('util');
        $apiClient = $this->get('apiclient');
        $request = $this->getRequest();
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());
        
        $assets = $apiClient->getEntities('Asset', 'get_report_assets', [ 'parameters' => ['id' => $reportId]]);
        $asset = $apiClient->getEntity('Asset', 'get_report_asset', [ 'parameters' => ['id' => $assetId]]);
        if (!in_array($assetId, $report->getAssets())) {
            throw new \RuntimeException("Asset not found.");
        }
        $form = $this->createForm(new FormDir\AssetType(), $asset);

        $form->handleRequest($request);

        // handle submit report
        if ($form->isValid()) {
            $asset = $form->getData();
            $apiClient->putC('update_report_asset', $asset);

            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        }
        
        return [
            'report' => $report,
            'assets' => $assets,
            'assetToEdit' => $asset,
            'client' => $client,
            'form' => $form->createView()
        ];
    }
    
    /**
     * @Route("/report/{reportId}/asset/{assetId}/delete/{confirmed}", name="asset_delete")
     * @Template("AppBundle:Asset:list.html.twig")
     */
    public function deleteAction($reportId, $assetId, $confirmed = false)
    {
        $util = $this->get('util');
        $apiClient = $this->get('apiclient');
        $request = $this->getRequest();
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());
        
        $assets = $apiClient->getEntities('Asset', 'get_report_assets', [ 'parameters' => ['id' => $reportId]]);
        $asset = $apiClient->getEntity('Asset', 'get_report_asset', [ 'parameters' => ['id' => $assetId]]);
        if (!in_array($assetId, $report->getAssets())) {
            throw new \RuntimeException("Asset not found.");
        }
        $form = $this->createForm(new FormDir\AssetType(), $asset);

        // handle submit report
        if ($confirmed) {
            $apiClient->delete('delete_report_asset', [ 'parameters' => [ 'id' => $assetId]]);
            
            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
            
        }
        
        return [
            'report' => $report,
            'assets' => $assets,
            'assetToEdit' => $asset,
            'client' => $client,
            'form' => $form->createView(),
            'showDeleteConfirm' => true
        ];
    }
    
    /**
     * --action [ list, add, edit, delete-confirm ]
     * @Route("/report/{reportId}/assets/{action}/{id}", 
     *        name="assets", 
     *        defaults={ "action" = "list", "id" = " "},
     *        requirements={"action"="(list|add|edit|delete-confirm)"}
     * )
     * @Template()
     */
    public function listAction($reportId, $action, $id)
    {
        $util = $this->get('util');
        $apiClient = $this->get('apiclient');
        $request = $this->getRequest();

        $report = $util->getReport($reportId, $this->getUser()->getId());

        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());

        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }

        $assets = $apiClient->getEntities('Asset', 'get_report_assets', [ 'parameters' => ['id' => $reportId]]);
        
        // if there are no assets and the report is not due, show new asset form
        if (empty($assets) && !$report->isDue()) {
            return $this->forward('AppBundle:Asset:addSelectTitle', array(
                'reportId'  => $reportId,
            ));

        }
        
        $noAssetsToAdd = $this->createForm(new FormDir\NoAssetToAddType());

        // handle no asset form
        $noAssetsToAdd->handleRequest($request);
        if ($noAssetsToAdd->get('saveNoAsset')->isClicked() && $noAssetsToAdd->isValid()) {
            $report->setNoAssetToAdd(true);
            $apiClient->putC('report/' . $report->getId(), $report);

            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $report->getId()]));
        }

        return [
            'report' => $report,
            'client' => $client,
            'action' => $action,
            'no_assets_to_add' => $noAssetsToAdd->createView(),
            'assets' => $assets,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }

}