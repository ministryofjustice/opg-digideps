<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report;
use App\Entity\User;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AssetController extends AbstractController
{
    private static $jmsGroups = [
        'asset',
        'asset-state',
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    /** @var StepRedirector */
    private $stepRedirector;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi,
        StepRedirector $stepRedirector,
        private ClientApi $clientApi,
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
        $this->stepRedirector = $stepRedirector;
    }

    /**
     * @Route("/report/{reportId}/assets", name="assets")
     *
     * @Template("@App/Report/Asset/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Report\Status::STATE_NOT_STARTED != $report->getStatus()->getAssetsState()['state']) {
            return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/exist", name="assets_exist")
     *
     * @Template("@App/Report/Asset/exist.html.twig")
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ('GET' == $request->getMethod() && $report->getAssets()) { // if assets are added, set form default to "Yes"
            $report->setNoAssetToAdd(0);
        }
        $form = $this->createForm(FormDir\YesNoType::class, $report, [
            'field' => 'noAssetToAdd',
            'translation_domain' => 'report-assets',
            'choices' => ['Yes' => 0, 'No' => 1],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($report->getNoAssetToAdd()) {
                case 0: // yes
                    return $this->redirectToRoute('assets_type', ['reportId' => $reportId]);
                case 1: // no
                    $this->restClient->put('report/'.$reportId, $report, ['noAssetsToAdd']);

                    return $this->redirectToRoute('assets_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('assets', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
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
     *
     * @Template("@App/Report/Asset/type.html.twig")
     */
    public function typeAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\Asset\AssetTypeTitle::class, new Report\AssetOther(), [
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     *
     * @Template("@App/Report/Asset/Other/add.html.twig")
     */
    public function otherAddAction(Request $request, $reportId, $title)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);
        $asset = new Report\AssetOther();
        $asset->setTitle($title);
        $asset->setReport($report);

        $form = $this->createForm(FormDir\Report\Asset\AssetTypeOther::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();
            $this->restClient->post("report/{$reportId}/asset", $asset);

            return $this->redirect($this->generateUrl('assets_add_another', ['reportId' => $reportId]));
        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('assets_type', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
            // avoid sending query string to GA containing user's data
            'gaCustomUrl' => $this->generateUrl('asset_other_add', ['reportId' => $reportId, 'title' => 'type']),
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/other/edit/{assetId}", name="asset_other_edit")
     *
     * @Template("@App/Report/Asset/Other/edit.html.twig")
     */
    public function otherEditAction(Request $request, $reportId, $assetId = null)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);
        if ($assetId) {
            $asset = $this->restClient->get("report/{$reportId}/asset/{$assetId}", 'Report\\Asset');
        } else {
            $asset = new Report\AssetOther();
            $asset->setReport($report);
        }

        $form = $this->createForm(FormDir\Report\Asset\AssetTypeOther::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();
            $this->restClient->put("report/{$reportId}/asset/{$assetId}", $asset);
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
     *
     * @Template("@App/Report/Asset/addAnother.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-assets']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     *
     * @Template("@App/Report/Asset/Property/step.html.twig")
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
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
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
            $asset = new Report\AssetProperty();
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
            'data' => $dataFromUrl,
        ]);

        // crete and handle form
        $form = $this->createForm(FormDir\Report\Asset\AssetTypeProperty::class, $asset, ['step' => $step]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();
            /* @var $asset Report\AssetProperty */

            // edit mode: save immediately and go back to summary page
            if ($assetId) {
                $this->restClient->put("report/{$reportId}/asset/{$assetId}", $asset);
                $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

                return $this->redirect($this->generateUrl('assets_summary', ['reportId' => $reportId]));
            }

            if (1 == $step) {
                $stepUrlData['address'] = $asset->getAddress();
                $stepUrlData['address2'] = $asset->getAddress2();
                $stepUrlData['postcode'] = $asset->getPostcode();
                $stepUrlData['county'] = $asset->getCounty();
            }

            if (2 == $step) {
                $stepUrlData['occupants'] = $asset->getOccupants();
            }

            if (3 == $step) {
                $stepUrlData['owned'] = $asset->getOwned();
                $stepUrlData['owned_p'] = $asset->getOwnedPercentage();
            }

            if (4 == $step) {
                $stepUrlData['has_mg'] = $asset->getHasMortgage();
                $stepUrlData['mg_oa'] = $asset->getMortgageOutstandingAmount();
            }
            if (5 == $step) {
                $stepUrlData['value'] = $asset->getValue();
            }
            if (6 == $step) {
                $stepUrlData['ser'] = $asset->getIsSubjectToEquityRelease();
            }
            if (7 == $step) {
                $stepUrlData['hc'] = $asset->getHasCharges();
            }

            // last step: save
            if ($step == $totalSteps) {
                $this->restClient->post("report/{$reportId}/asset", $asset);

                return $this->redirect($this->generateUrl('assets_add_another', ['reportId' => $reportId]));
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData,
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
            'gaCustomUrl' => $request->getPathInfo(), // avoid sending query string to GA containing user's data
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/summary", name="assets_summary")
     *
     * @Template("@App/Report/Asset/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Report\Status::STATE_NOT_STARTED == $report->getStatus()->getAssetsState()['state']) {
            return $this->redirect($this->generateUrl('assets', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/report/{reportId}/assets/{assetId}/delete", name="asset_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $assetId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($report->hasAssetWithId($assetId)) {
                $this->restClient->delete("/report/{$reportId}/asset/{$assetId}");
                $request->getSession()->getFlashBag()->add('notice', 'Asset removed');
            }

            return $this->redirect($this->generateUrl('assets_summary', ['reportId' => $reportId]));
        }

        $asset = $this->restClient->get("report/{$reportId}/asset/{$assetId}", 'Report\\Asset');

        if ($asset instanceof Report\AssetProperty) {
            $summary = [
                ['label' => 'deletePage.summary.type', 'value' => 'deletePage.summary.property', 'format' => 'translate'],
                ['label' => 'deletePage.summary.address', 'value' => implode(', ', $asset->getAddressValidLines())],
                ['label' => 'deletePage.summary.value', 'value' => $asset->getValue(), 'format' => 'money'],
            ];
        } else {
            $summary = [
                ['label' => 'deletePage.summary.type', 'value' => $asset->getTitle()],
                ['label' => 'deletePage.summary.description', 'value' => $asset->getDescription()],
                ['label' => 'deletePage.summary.value', 'value' => $asset->getValue(), 'format' => 'money'],
                ['label' => 'deletePage.summary.valuationDate', 'value' => $asset->getValuationDate(), 'format' => 'date'],
            ];
        }

        return [
            'translationDomain' => 'report-assets',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('assets_summary', ['reportId' => $reportId]),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'assets';
    }
}
