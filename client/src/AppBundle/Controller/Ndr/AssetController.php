<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\NdrStatusService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AssetController extends AbstractController
{
    private static $jmsGroups = ['ndr-asset'];

    /**
     * @Route("/ndr/{ndrId}/assets", name="ndr_assets")
     * @Template("AppBundle:Ndr/Asset:start.html.twig")
     *
     * @param int $ndrId
     *
     * @return array
     */
    public function startAction($ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getAssetsState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_assets_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/assets/exist", name="ndr_assets_exist")
     * @Template("AppBundle:Ndr/Asset:exist.html.twig")
     */
    public function existAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($request->getMethod() == 'GET' && $ndr->getAssets()) { // if assets are added, set form default to "Yes"
            $ndr->setNoAssetToAdd(0);
        }
        $form = $this->createForm(FormDir\YesNoType::class, $ndr, [
            'field'              => 'noAssetToAdd',
            'translation_domain' => 'ndr-assets',
            'choices'            => ['Yes'=>0, 'No'=>1]
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($ndr->getNoAssetToAdd()) {
                case 0: // yes
                    return $this->redirectToRoute('ndr_assets_type', ['ndrId' => $ndrId,]);
                case 1: //no
                    $this->getRestClient()->put('ndr/' . $ndrId, $ndr, ['noAssetsToAdd']);
                    return $this->redirectToRoute('ndr_assets_summary', ['ndrId' => $ndrId]);
            }
        }

        $backLink = $this->generateUrl('ndr_assets', ['ndrId' => $ndrId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('ndr_assets_summary', ['ndrId' => $ndrId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/assets/step-type", name="ndr_assets_type")
     * @Template("AppBundle:Ndr/Asset:type.html.twig")
     */
    public function typeAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Ndr\Asset\AssetTypeTitle::class, new EntityDir\Ndr\AssetOther(), [
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $title = $form->getData()->getTitle();
            switch ($title) {
                case 'Property':
                    return $this->redirect($this->generateUrl('ndr_assets_property_step', ['ndrId' => $ndrId, 'step' => 1]));
                default:
                    return $this->redirect($this->generateUrl('ndr_asset_other_add', ['ndrId' => $ndrId, 'title' => $title]));
            }
        }

        return [
            'ndr' => $ndr,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('ndr_assets', ['ndrId' => $ndr->getId()]),
            'skipLink' => null,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/assets/other/{title}/add", name="ndr_asset_other_add")
     * @Template("AppBundle:Ndr/Asset/Other:add.html.twig")
     */
    public function otherAddAction(Request $request, $ndrId, $title)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $asset = new EntityDir\Ndr\AssetOther();
        $asset->setTitle($title);
        $asset->setndr($ndr);

        $form = $this->createForm(FormDir\Ndr\Asset\AssetTypeOther::class, $asset);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $asset = $form->getData();
            $this->getRestClient()->post("ndr/{$ndrId}/asset", $asset);

            return $this->redirect($this->generateUrl('ndr_assets_add_another', ['ndrId' => $ndrId]));
        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('ndr_assets_type', ['ndrId' => $ndrId]),
            'form' => $form->createView(),
            'ndr' => $ndr,
            // avoid sending query string to GA containing user's data
            'gaCustomUrl' => $this->generateUrl('ndr_asset_other_add', ['ndrId'=>$ndrId, 'title'=>'type'])
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/assets/other/edit/{assetId}", name="ndr_asset_other_edit")
     * @Template("AppBundle:Ndr/Asset/Other:edit.html.twig")
     */
    public function otherEditAction(Request $request, $ndrId, $assetId = null)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($assetId) {
            $asset = $this->getRestClient()->get("ndr/{$ndrId}/asset/{$assetId}", 'Ndr\\Asset');
        } else {
            $asset = new EntityDir\Ndr\AssetOther();
            $asset->setndr($ndr);
        }


        $form = $this->createForm(FormDir\Ndr\Asset\AssetTypeOther::class, $asset);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $asset = $form->getData();
            $this->getRestClient()->put("ndr/{$ndrId}/asset/{$assetId}", $asset);
            $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

            return $this->redirect($this->generateUrl('ndr_assets', ['ndrId' => $ndrId]));
        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('ndr_assets_summary', ['ndrId' => $ndrId]),
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/assets/add_another", name="ndr_assets_add_another")
     * @Template("AppBundle:Ndr/Asset:addAnother.html.twig")
     */
    public function addAnotherAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $ndr, ['translation_domain' => 'ndr-assets']);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('ndr_assets_type', ['ndrId' => $ndrId, 'from' => 'another']);
                case 'no':
                    return $this->redirectToRoute('ndr_assets_summary', ['ndrId' => $ndrId]);
            }
        }

        return [
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/assets/property/step{step}/{assetId}", name="ndr_assets_property_step", requirements={"step":"\d+"})
     * @Template("AppBundle:Ndr/Asset/Property:step.html.twig")
     */
    public function propertyStepAction(Request $request, $ndrId, $step, $assetId = null)
    {
        $totalSteps = 8;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('ndr_assets_summary', ['ndrId' => $ndrId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('ndr_assets_type', 'ndr_assets_property_step', 'ndr_assets_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['ndrId' => $ndrId, 'assetId' => $assetId]);

        if ($assetId) { // edit asset
            $assets = array_filter($ndr->getAssets(), function ($t) use ($assetId) {
                return $t->getId() == $assetId;
            });
            $asset = array_shift($assets);
            $stepRedirector->setFromPage('summary');
        } else { // add new asset
            $asset = new EntityDir\Ndr\AssetProperty();
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
        $form = $this->createForm(FormDir\Ndr\Asset\AssetTypeProperty::class, $asset, ['step' => $step]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $asset = $form->getData();
            /* @var $asset EntityDir\Ndr\AssetProperty */

            // edit mode: save immediately and go back to summary page
            if ($assetId) {
                $this->getRestClient()->put("ndr/{$ndrId}/asset/{$assetId}", $asset);
                $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

                return $this->redirect($this->generateUrl('ndr_assets_summary', ['ndrId' => $ndrId]));
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
                $this->getRestClient()->post("ndr/{$ndrId}/asset", $asset);

                return $this->redirect($this->generateUrl('ndr_assets_add_another', ['ndrId' => $ndrId]));
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'asset' => $asset,
            'ndr' => $ndr,
            'step' => $step,
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
            'gaCustomUrl' => $request->getPathInfo() // avoid sending query string to GA containing user's data
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/assets/summary", name="ndr_assets_summary")
     * @Template("AppBundle:Ndr/Asset:summary.html.twig")
     *
     * @param int $ndrId
     *
     * @return array
     */
    public function summaryAction($ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getAssetsState()['state'] == NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('ndr_assets', ['ndrId' => $ndrId]));
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/assets/{assetId}/delete", name="ndr_asset_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $ndrId, $assetId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

            if ($ndr->hasAssetWithId($assetId)) {
                $this->getRestClient()->delete("/ndr/{$ndrId}/asset/{$assetId}");
                $request->getSession()->getFlashBag()->add('notice', 'Asset removed');
            }

            return $this->redirect($this->generateUrl('ndr_assets', ['ndrId' => $ndrId]));
        }

        $asset = $this->getRestClient()->get("ndr/{$ndrId}/asset/{$assetId}", 'Ndr\\Asset');

        if ($asset instanceof EntityDir\Ndr\AssetProperty) {
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
            'translationDomain' => 'ndr-assets',
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('ndr_assets_summary', ['ndrId' => $ndrId]),
        ];
    }
}
