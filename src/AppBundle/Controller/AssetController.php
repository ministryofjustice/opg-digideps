<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report")
 */
class AssetController extends Controller
{


    /**
     * Form to select asset title (dropdown only)
     * when submitted and valid, redirects to 'asset_add_complete'.
     * 
     * When JS is enabled, there the content of that page is auto-loaded via AJAX
     *  
     * @Route("/{reportId}/assets/add-select-title", name="asset_add_select_title")
     * @Template("AppBundle:Asset:addSelectTitle.html.twig")
     */
    public function addSelectTitleAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $form = $this->createForm('asset_title', new EntityDir\Asset, [
            'action' => $this->generateUrl('asset_add_select_title', [ 'reportId' => $reportId])
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->redirect($this->generateUrl('asset_add_complete', [ 'reportId' => $reportId, 'title' => $form->getData()->getTitle()]));
        }

        
        return [
            'report' => $report,
            'client' => $report->getClientObject(),
            'form' => $form->createView(),
            'showCancelLink' => count($report->getAssets()) > 0,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }


    /**
     * Shows the full add asset form
     * 
     * @Route("/{reportId}/assets/add-complete/{title}", name="asset_add_complete")
     * @Template("AppBundle:Asset:addComplete.html.twig")
     */
    public function addCompleteAction(Request $request, $reportId, $title)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        // [.. change form and template (or forward) depending on the asset title ]
        $asset = new EntityDir\Asset();
        $asset->setTitle($title);
        $form = $this->createForm(new FormDir\AssetType(), $asset, [
            'action' => $this->generateUrl('asset_add_complete', [ 'reportId' => $reportId, 'title' => $title])
        ]);

        $form->handleRequest($request);

        // handle submit report
        if ($form->isValid()) {
            $asset = $form->getData();
            $asset->setReport($reportId);
            $this->get('restClient')->post('report/upsert-asset', $asset);

            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'client' => $report->getClientObject(),
            'form' => $form->createView(),
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }


    /**
     * Edit a record
     * the edit form is "inline" so it needs 
     * 
     * @Route("/{reportId}/assets/{assetId}/edit", name="asset_edit")
     * @Template("AppBundle:Asset:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $assetId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        if (!in_array($assetId, $report->getAssets())) {
            throw new \RuntimeException("Asset not found.");
        }
        $asset = $this->get('restClient')->get('report/get-asset/' . $assetId, 'Asset');
        $form = $this->createForm(new FormDir\AssetType(), $asset);

        $form->handleRequest($request);

        // handle submit report
        if ($form->isValid()) {
            $asset = $form->getData();
            $this->get('restClient')->put('report/upsert-asset', $asset);

            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        }

        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }

        return [
            'report' => $report,
            'assetToEdit' => $asset,
            'client' => $report->getClientObject(),
            'form' => $form->createView(),
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }


    /**
     * Delete asset
     * Inline
     * similar to Edit
     * 
     * @Route("/{reportId}/assets/{assetId}/delete/{confirmed}", name="asset_delete")
     * @Template("AppBundle:Asset:delete.html.twig")
     */
    public function deleteAction($reportId, $assetId, $confirmed = false)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        if (!in_array($assetId, $report->getAssets())) {
            throw new \RuntimeException("Asset not found.");
        }
        $asset = $this->get('restClient')->get('report/get-asset/' . $assetId, 'Asset');
        $form = $this->createForm(new FormDir\AssetType(), $asset);

        // handle delete
        if ($confirmed) {
            $this->get('restClient')->delete('report/delete-asset/' . $assetId);

            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'assetToEdit' => $asset,
            'client' => $report->getClientObject(),
            'form' => $form->createView(),
        ];
    }


    /**
     * List assets and also handle no-asset checkbox-form
     * 
     * @Route("/{reportId}/assets", name="assets")
     * @Template("AppBundle:Asset:list.html.twig")
     */
    public function listAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        // if there are no assets and the report is not due, show new asset form
        if (empty($report->getAssets()) && !$report->isDue()) {
            return $this->forward('AppBundle:Asset:addSelectTitle', array(
                    'reportId' => $reportId,
            ));
        }

        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }
        
        list ($noAssetsToAdd, $isFormValid) = $this->handleNoAssetsForm($request, $report);
        if ($isFormValid) {
            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'client' => $report->getClientObject(),
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }


    /**
     * List assets and also handle no-asset checkbox-form
     * 
     * @Template("AppBundle:Asset:_list.html.twig")
     */
    public function _listAction($reportId, $assetToEdit = null, $editForm = null, $showDeleteConfirm = false, $showEditLink = true)
    {
        $report = $this->get('util')->getReport($reportId, $this->getUser()->getId());

        $assets = $this->get('restClient')->get('report/get-assets/' . $reportId, 'Asset[]');

        return [
            'report' => $report,
            'assetToEdit' => $assetToEdit,
            'assets' => $assets,
            'editForm' => $editForm,
            'showDeleteConfirm' => $showDeleteConfirm,
            'showEditLink' => $showEditLink
        ];
    }


    /**
     * Return the small template with the checkbox or the string indicating the selection
     * 
     * @Template("AppBundle:Asset:_noAssets.html.twig")
     */
    public function _noAssetsAction(Request $request, $reportId)
    {
        $report = $this->get('util')->getReport($reportId, $this->getUser()->getId());

        list ($noAssetsToAdd, $isFormValid) = $this->handleNoAssetsForm($request, $report);

        return [
            'report' => $report,
            'no_assets_to_add' => $noAssetsToAdd->createView(),
        ];
    }
    
    /**
     * create and handle request for noAssets form
     * The form posts into "list" action.
     * 
     * @param Request $request
     * @param EntityDir\Report $report
     * 
     * @return array [FormDir\NoAssetToAddType, null]
     */
    private function handleNoAssetsForm(Request $request, EntityDir\Report $report)
    {
        $noAssetsToAdd = $this->createForm(new FormDir\NoAssetToAddType(), null, [
            'action' => $this->generateUrl('assets', [ 'reportId' => $report->getId()])
        ]);

        // handle no asset form
        $noAssetsToAdd->handleRequest($request);
        $isFormValid = false;
        if ($noAssetsToAdd->get('saveNoAsset')->isClicked() && $noAssetsToAdd->isValid()) {
            $report->setNoAssetToAdd(true);
            $this->get('restClient')->put('report/' . $report->getId(), $report);
            $isFormValid = true;
        }
        
        return [$noAssetsToAdd, $isFormValid];
    }


    /**
     * 
     * @param integer $reportId
     * @return EntityDir\Report
     * 
     * @throws \RuntimeException if report is submitted
     */
    private function getReportIfReportNotSubmitted($reportId, $addClient = true)
    {
        $util = $this->get('util');

        $report = $util->getReport($reportId, $this->getUser()->getId());
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        if ($addClient) {
            $client = $util->getClient($report->getClient(), $this->getUser()->getId());
            $report->setClientObject($client);
        }

        return $report;
    }

}