<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportStatusService;
use AppBundle\Service\StepRedirector;
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
                    return $this->redirectToRoute('assets_select_title', ['reportId' => $reportId,]);
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
     * @Route("/report/{reportId}/assets/step-select-title", name="assets_select_title")
     * @Template()
     */
    public function stepSelectTitleAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['asset']);
        $form = $this->createForm('asset_title', new EntityDir\Report\AssetOther(), [
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $title = $form->getData()->getTitle();
            switch ($title) {
                case 'Property':
                    return $this->redirect($this->generateUrl('assets_property_step', ['reportId' => $reportId, 'step'=>1]));
                default:
                    return $this->redirect($this->generateUrl('asset_other_add', ['reportId' => $reportId, 'title'=>$title]));
            }
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('assets', ['reportId'=>$report->getId()]),
            'skipLink' => null,
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/other/{title}/add", name="asset_other_add")
     * @Template("AppBundle:Report/Asset/Other:add.html.twig")
     */
    public function otherAddAction(Request $request, $reportId, $title)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $asset = new Report\AssetOther();
        $asset->setTitle($title);
        $asset->setReport($report);

        $form = $this->createForm(new FormDir\Report\Asset\AssetTypeOther(), $asset);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $asset = $form->getData();
            $this->getRestClient()->post("report/{$reportId}/asset", $asset);

            return $this->redirect($this->generateUrl('assets_add_another', ['reportId' => $reportId]));
        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('assets_select_title', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/other/edit/{assetId}", name="asset_other_edit")
     * @Template("AppBundle:Report/Asset/Other:edit.html.twig")
     */
    public function otherEditAction(Request $request, $reportId, $assetId = null)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);
        if ($assetId){
            $asset = $this->getRestClient()->get("report/{$reportId}/asset/{$assetId}", 'Report\\Asset');
        } else {
            $asset = new Report\AssetOther();
            $asset->setReport($report);
        }


        $form = $this->createForm(new FormDir\Report\Asset\AssetTypeOther(), $asset);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $asset = $form->getData();
            $this->getRestClient()->put("report/{$reportId}/asset/{$assetId}", $asset);
            $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

            return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));

        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('assets_summary', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/assets/add_another", name="assets_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $form = $this->createForm(new FormDir\Report\Asset\AssetAddAnotherType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('assets_select_title', ['reportId' => $reportId, 'from'=>'another']);
                case 'no':
                    return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/assets/property/step{step}/{assetId}", name="assets_property_step", requirements={"step":"\d+"})
     * @Template("AppBundle:Report/Asset/Property:step.html.twig")
     */
    public function propertyStepAction(Request $request, $reportId, $step, $assetId = null)
    {
        $totalSteps = 1;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->getReportIfReportNotSubmitted($reportId, ['asset']);
        $fromPage = $request->get('from');

        /* @var $stepRedirector StepRedirector */
        $stepRedirector = $this->get('stepRedirector')
            ->setRoutes('assets_select_title', 'assets_property_step', 'assets_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps(1)
            ->setRouteBaseParams(['reportId'=>$reportId, 'assetId' => $assetId]);


        // create (add mode) or load assets (edit mode)
        if ($assetId) {
            $assets = array_filter($report->getAssets(), function($t) use ($assetId) {
                return $t->getId() == $assetId;
            });
            $asset = array_shift($assets);
        } else {
            $asset = new EntityDir\Report\AssetProperty();
        }

        // add URL-data into model
//        isset($dataFromUrl['group']) && $assets->setGroup($dataFromUrl['group']);
//        isset($dataFromUrl['category']) && $assets->setCategory($dataFromUrl['category']);
//        $stepRedirector->setStepUrlAdditionalParams([
//            'data' => $dataFromUrl
//        ]);

        // crete and handle form
        $form = $this->createForm(new FormDir\Report\Asset\AssetTypeProperty(), $asset);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {

            $asset = $form->getData();

            if ($assetId) {
                $this->getRestClient()->put("report/{$reportId}/asset/{$assetId}", $asset);

                return $this->redirect($this->generateUrl('assets_summary', ['reportId' => $reportId]));
            } else {
                $this->getRestClient()->post("report/{$reportId}/asset", $asset);

                return $this->redirect($this->generateUrl('assets_add_another', ['reportId' => $reportId]));
            }


//            $stepRedirector->setStepUrlAdditionalParams([
//                'data' => $stepUrlData
//            ]);

//            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'asset' => $asset,
            'report' => $report,
            'step' => $step,
            'reportStatus' => new ReportStatusService($report),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
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

    /**
     * @Route("/{reportId}/assets/{assetId}/delete", name="asset_delete")
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $assetId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['asset']);

        if ($report->hasAssetWithId($assetId)) {
            $this->getRestClient()->delete("/report/{$reportId}/asset/{$assetId}");
        }

        return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
    }

}
