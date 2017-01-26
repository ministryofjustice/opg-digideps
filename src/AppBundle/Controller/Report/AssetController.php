<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportStatusService;
use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AssetController extends AbstractController
{
    private static $jmsGroups = [
        'asset',
    ];

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
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
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
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($request->getMethod() == 'GET' && $report->getAssets()) { // if assets are added, set form default to "Yes"
            $report->setNoAssetToAdd(0);
        }
        $form = $this->createForm(new FormDir\Report\Asset\AssetExistType(), $report);

        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($report->getNoAssetToAdd()) {
                case 0: // yes
                    return $this->redirectToRoute('assets_type', ['reportId' => $reportId,]);
                case 1: //no
                    $this->get('rest_client')->put('report/' . $reportId, $report, ['noAssetsToAdd']);
                    return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('assets', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('assets_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/step-type", name="assets_type")
     * @Template()
     */
    public function typeAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm('asset_title', new EntityDir\Report\AssetOther(), [
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $title = $form->getData()->getTitle();
            switch ($title) {
                case 'Property':
                    return $this->redirect($this->generateUrl('assets_property_step', ['reportId' => $reportId, 'step' => 1]));
                default:
                    return $this->redirect($this->generateUrl('asset_other_add', ['reportId' => $reportId, 'title' => $title]));
            }
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('assets', ['reportId' => $report->getId()]),
            'skipLink' => null,
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/other/{title}/add", name="asset_other_add")
     * @Template("AppBundle:Report/Asset/Other:add.html.twig")
     */
    public function otherAddAction(Request $request, $reportId, $title)
    {
        $report = $this->getReportIfNotSubmitted($reportId);
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
            'backLink' => $this->generateUrl('assets_type', ['reportId' => $reportId]),
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
        $report = $this->getReportIfNotSubmitted($reportId);
        if ($assetId) {
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
            'backLink' => $this->generateUrl('assets_summary', ['reportId' => $reportId]),
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
        $report = $this->getReportIfNotSubmitted($reportId);

        $form = $this->createForm(new FormDir\Report\Asset\AssetAddAnotherType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('assets_type', ['reportId' => $reportId, 'from' => 'another']);
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
        $totalSteps = 8;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');


        $stepRedirector = $this->stepRedirector()
            ->setRoutes('assets_type', 'assets_property_step', 'assets_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'assetId' => $assetId]);


        if ($assetId) { // edit asset
            $assets = array_filter($report->getAssets(), function ($t) use ($assetId) {
                return $t->getId() == $assetId;
            });
            $asset = array_shift($assets);
            $stepRedirector->setFromPage('summary');
        } else { // add new asset
            $asset = new EntityDir\Report\AssetProperty();
        }

        // add URL-data into model
        isset($dataFromUrl['address']) && $asset->setAddress($dataFromUrl['address']);
        isset($dataFromUrl['address2']) && $asset->setAddress2($dataFromUrl['address2']);
        isset($dataFromUrl['postcode']) && $asset->setPostcode($dataFromUrl['postcode']);
        isset($dataFromUrl['county']) && $asset->setCounty($dataFromUrl['county']);
        isset($dataFromUrl['occupants']) && $asset->setOccupants($dataFromUrl['occupants']);
        isset($dataFromUrl['owned']) && $asset->setOwned($dataFromUrl['owned']);
        isset($dataFromUrl['owned_p']) && $asset->setOwnedPercentage($dataFromUrl['owned_p']);
        isset($dataFromUrl['has_mg']) && $asset->setHasMortgage($dataFromUrl['has_mg']);
        isset($dataFromUrl['mg_oa']) && $asset->setMortgageOutstandingAmount($dataFromUrl['mg_oa']);
        isset($dataFromUrl['value']) && $asset->setValue($dataFromUrl['value']);
        isset($dataFromUrl['ser']) && $asset->setIsSubjectToEquityRelease($dataFromUrl['ser']);
        isset($dataFromUrl['hc']) && $asset->setHasCharges($dataFromUrl['hc']);
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl
        ]);

        // crete and handle form
        $form = $this->createForm(new FormDir\Report\Asset\AssetTypeProperty($step), $asset);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {

            $asset = $form->getData();
            /* @var $asset Report\AssetProperty */

            // edit mode: save immediately and go back to summary page
            if ($assetId) {
                $this->getRestClient()->put("report/{$reportId}/asset/{$assetId}", $asset);
                $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

                return $this->redirect($this->generateUrl('assets_summary', ['reportId' => $reportId]));
            }

            if ($step == 1) {
                $stepUrlData['address'] = $asset->getAddress();
                $stepUrlData['address2'] = $asset->getAddress2();
                $stepUrlData['postcode'] = $asset->getPostcode();
                $stepUrlData['county'] = $asset->getCounty();
            }

            if ($step == 2) {
                $stepUrlData['occupants'] = $asset->getOccupants();
            }

            if ($step == 3) {
                $stepUrlData['owned'] = $asset->getOwned();
                $stepUrlData['owned_p'] = $asset->getOwnedPercentage();
            }

            if ($step == 4) {
                $stepUrlData['has_mg'] = $asset->getHasMortgage();
                $stepUrlData['mg_oa'] = $asset->getMortgageOutstandingAmount();
            }
            if ($step == 5) {
                $stepUrlData['value'] = $asset->getValue();
            }
            if ($step == 6) {
                $stepUrlData['ser'] = $asset->getIsSubjectToEquityRelease();
            }
            if ($step == 7) {
                $stepUrlData['hc'] = $asset->getHasCharges();
            }

            // last step: save
            if ($step == $totalSteps) {
                $this->getRestClient()->post("report/{$reportId}/asset", $asset);

                return $this->redirect($this->generateUrl('assets_add_another', ['reportId' => $reportId]));
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'asset' => $asset,
            'report' => $report,
            'step' => $step,
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
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (count($report->getAssets()) == 0 && $report->getNoAssetToAdd() === null) {
            return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/{assetId}/delete", name="asset_delete")
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $assetId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->hasAssetWithId($assetId)) {
            $this->getRestClient()->delete("/report/{$reportId}/asset/{$assetId}");
            $request->getSession()->getFlashBag()->add('notice', 'Asset removed');
        }

        return $this->redirect($this->generateUrl('assets_summary', ['reportId' => $reportId]));
    }

}
