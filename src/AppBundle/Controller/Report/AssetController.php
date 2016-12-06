<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/report")
 */
class AssetController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/assets", name="assets")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['asset']);
        if (count($report->getAssets()) > 0 || $report->getNoAssetToAdd()) {
            return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/exist", name="assets_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['asset']);
        $form = $this->createForm(new FormDir\Report\AssetExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($report->getNoAssetToAdd()) {
                case 0: // yes
                    return $this->redirectToRoute('assets_step', ['reportId' => $reportId, 'step'=>1]);
                case 1: //no
                    $this->get('restClient')->put('report/' . $reportId, $report, ['noAssetsToAdd']);
                    return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('assets', ['reportId'=>$reportId]);
        if ( $request->get('from') == 'summary') {
            $backLink = $this->generateUrl('assets_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/assets/summary", name="assets_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['asset']);
        if (count($report->getAssets()) == 0 && $report->getNoAssetToAdd() === null) {
            return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }
    
    
//    /**
//     * List assets and also handle no-asset checkbox-form.
//     *
//     * @Route("/{reportId}/assets", name="assets")
//     * @Template("AppBundle:Report/Asset:list.html.twig")
//     */
//    public function listAction(Request $request, $reportId)
//    {
//        $report = $this->getReportIfReportNotSubmitted($reportId, ['asset']);
//        $assets = $report->getAssets();
//
//        // if there are no assets and the report is not due, show new asset form
//        if (empty($assets) && !$report->isDue()) {
//            return $this->redirect($this->generateUrl('asset_add_select_title', ['reportId' => $reportId]));
//        }
//
//        return [
//            'report' => $report,
//            'assets' => $assets,
//        ];
//    }
//
//    /**
//     * Form to select asset title (dropdown only)
//     * when submitted and valid, redirects to 'asset_add_complete'.
//     *
//     * When JS is enabled, there the content of that page is auto-loaded via AJAX
//     *
//     * @Route("/{reportId}/assets/add", name="asset_add_select_title")
//     * @Template("AppBundle:Report/Asset:addSelectTitle.html.twig")
//     */
//    public function addSelectTitleAction(Request $request, $reportId)
//    {
//        $report = $this->getReportIfReportNotSubmitted($reportId);
//
//        $form = $this->createForm('asset_title', new EntityDir\Report\AssetOther(), [
//            'action' => $this->generateUrl('asset_add_select_title', ['reportId' => $reportId]),
//        ]);
//
//        $form->handleRequest($request);
//        if ($form->isValid()) {
//            return $this->redirect($this->generateUrl('asset_add_complete', ['reportId' => $reportId, 'title' => $form->getData()->getTitle()]));
//        }
//
//        return [
//            'report' => $report,
//            'form' => $form->createView(),
//            'showCancelLink' => count($report->getAssets()) > 0,
//        ];
//    }
//
//    /**
//     * Shows the full add asset form.
//     *
//     * @Route("/{reportId}/assets/add-complete/{title}", name="asset_add_complete")
//     * @Template("AppBundle:Report/Asset:addComplete.html.twig")
//     */
//    public function addCompleteAction(Request $request, $reportId, $title)
//    {
//        $report = $this->getReportIfReportNotSubmitted($reportId);
//
//        // [.. change form and template (or forward) depending on the asset title ]
//        $asset = EntityDir\Report\Asset::factory($title);
//
//        $form = $this->createForm(FormDir\Report\Asset\AbstractAssetType::factory($title), $asset, [
//            'action' => $this->generateUrl('asset_add_complete', ['reportId' => $reportId, 'title' => $title]),
//        ]);
//
//        $form->handleRequest($request);
//
//        // handle submit report
//        if ($form->isValid()) {
//            $asset = $form->getData();
//            $asset->setReport($report);
//            $this->getRestClient()->post("report/{$reportId}/asset", $asset);
//            $report->setNoAssetToAdd(false);
//            $this->getRestClient()->put('report/'.$reportId, $report);
//
//            return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
//        }
//
//        return [
//            'report' => $report,
//            'form' => $form->createView(),
//            'asset' => $asset,
//            'titleLcFirst' => lcfirst($title),
//        ];
//    }
//
//    /**
//     * Edit a record
//     * the edit form is "inline" so it needs.
//     *
//     * @Route("/{reportId}/assets/{assetId}/edit", name="asset_edit")
//     * @Template("AppBundle:Report/Asset:edit.html.twig")
//     */
//    public function editAction(Request $request, $reportId, $assetId)
//    {
//        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'client', 'asset', 'accounts']);
//        if (!$report->hasAssetWithId($assetId)) {
//            throw new \RuntimeException('Asset not found.');
//        }
//        $asset = $this->getRestClient()->get("report/{$reportId}/asset/{$assetId}", 'Report\\Asset');
//        $form = $this->createForm(FormDir\Report\Asset\AbstractAssetType::factory($asset->getType()), $asset);
//
//        $form->handleRequest($request);
//
//        // handle submit report
//        if ($form->isValid()) {
//            $asset = $form->getData();
//            $this->getRestClient()->put("report/{$reportId}/asset/{$assetId}", $asset);
//
//            return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
//        }
//
//        return [
//            'report' => $report,
//            'assetToEdit' => $asset,
//            'form' => $form->createView(),
//        ];
//    }
//
//    /**
//     * @Route("/{reportId}/assets/{id}/delete", name="asset_delete")
//     *
//     * @param int $id
//     *
//     * @return RedirectResponse
//     */
//    public function deleteAction($reportId, $id)
//    {
//        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'client', 'asset', 'accounts']);
//
//        if ($report->hasAssetWithId($id)) {
//            $this->getRestClient()->delete("/report/{$reportId}/asset/{$id}");
//        }
//
//        return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
//    }
//
//    /**
//     * Sub controller action called when the no decision form is embedded in another page.
//     *
//     * @Template("AppBundle:Report/Asset:_noAssets.html.twig")
//     */
//    public function _noAssetsAction(Request $request, $reportId)
//    {
//        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'client', 'asset', 'accounts']);
//        $form = $this->createForm(new FormDir\Report\NoAssetToAddType(), $report, []);
//        $form->handleRequest($request);
//
//        if ($request->getMethod() == 'POST') {
//            $this->getRestClient()->put('report/'.$reportId, $form->getData(), ['noAssetsToAdd']);
//        }
//
//        return [
//            'form' => $form->createView(),
//            'report' => $report,
//        ];
//    }
}
