<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AssetController extends AbstractController
{
    private static $jmsGroups = ['odr-asset'];

    /**
     * @Route("/odr/{odrId}/assets", name="odr_assets")
     * @Template()
     *
     * @param int $odrId
     *
     * @return array
     */
    public function startAction($odrId)
    {
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        if (count($odr->getAssets()) > 0 || $odr->getNoAssetToAdd()) {
            return $this->redirectToRoute('odr_assets_summary', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/assets/exist", name="odr_assets_exist")
     * @Template()
     */
    public function existAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        if ($request->getMethod() == 'GET' && $odr->getAssets()) { // if assets are added, set form default to "Yes"
            $odr->setNoAssetToAdd(0);
        }
        $form = $this->createForm(new FormDir\Odr\Asset\AssetExistType(), $odr);

        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($odr->getNoAssetToAdd()) {
                case 0: // yes
                    return $this->redirectToRoute('odr_assets_type', ['odrId' => $odrId,]);
                case 1: //no
                    $this->get('restClient')->put('odr/' . $odrId, $odr, ['noAssetsToAdd']);
                    return $this->redirectToRoute('odr_assets_summary', ['odrId' => $odrId]);
            }
        }

        $backLink = $this->generateUrl('odr_assets', ['odrId' => $odrId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('odr_assets_summary', ['odrId' => $odrId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/assets/step-type", name="odr_assets_type")
     * @Template()
     */
    public function typeAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        $form = $this->createForm('odr_asset_title', new EntityDir\Odr\AssetOther(), [
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $title = $form->getData()->getTitle();
            switch ($title) {
                case 'Property':
                    return $this->redirect($this->generateUrl('odr_assets_property_step', ['odrId' => $odrId, 'step' => 1]));
                default:
                    return $this->redirect($this->generateUrl('asset_other_add', ['odrId' => $odrId, 'title' => $title]));
            }
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('odr_assets', ['odrId' => $odr->getId()]),
            'skipLink' => null,
        ];
    }

    /**
     * @Route("/odr/{odrId}/assets/other/{title}/add", name="asset_other_add")
     * @Template("AppBundle:odr/Asset/Other:add.html.twig")
     */
    public function otherAddAction(Request $request, $odrId, $title)
    {
        $odr = $this->getOdr($odrId);
        $asset = new EntityDir\Odr\AssetOther();
        $asset->setTitle($title);
        $asset->setodr($odr);

        $form = $this->createForm(new FormDir\Odr\Asset\AssetTypeOther(), $asset);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $asset = $form->getData();
            $this->getRestClient()->post("odr/{$odrId}/asset", $asset);

            return $this->redirect($this->generateUrl('odr_assets_add_another', ['odrId' => $odrId]));
        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('odr_assets_type', ['odrId' => $odrId]),
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/assets/other/edit/{assetId}", name="asset_other_edit")
     * @Template("AppBundle:odr/Asset/Other:edit.html.twig")
     */
    public function otherEditAction(Request $request, $odrId, $assetId = null)
    {
        $odr = $this->getOdr($odrId);
        if ($assetId) {
            $asset = $this->getRestClient()->get("odr/{$odrId}/asset/{$assetId}", 'Odr\\Asset');
        } else {
            $asset = new EntityDir\Odr\AssetOther();
            $asset->setodr($odr);
        }


        $form = $this->createForm(new FormDir\Odr\Asset\AssetTypeOther(), $asset);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $asset = $form->getData();
            $this->getRestClient()->put("odr/{$odrId}/asset/{$assetId}", $asset);
            $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

            return $this->redirect($this->generateUrl('odr_assets', ['odrId' => $odrId]));

        }

        return [
            'asset' => $asset,
            'backLink' => $this->generateUrl('odr_assets_summary', ['odrId' => $odrId]),
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }


    /**
     * @Route("/odr/{odrId}/assets/add_another", name="odr_assets_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId);

        $form = $this->createForm(new FormDir\Odr\Asset\AssetAddAnotherType(), $odr);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('odr_assets_type', ['odrId' => $odrId, 'from' => 'another']);
                case 'no':
                    return $this->redirectToRoute('odr_assets_summary', ['odrId' => $odrId]);
            }
        }

        return [
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }


    /**
     * @Route("/odr/{odrId}/assets/property/step{step}/{assetId}", name="odr_assets_property_step", requirements={"step":"\d+"})
     * @Template("AppBundle:odr/Asset/Property:step.html.twig")
     */
    public function propertyStepAction(Request $request, $odrId, $step, $assetId = null)
    {
        $totalSteps = 8;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('odr_assets_summary', ['odrId' => $odrId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        /* @var $stepRedirector StepRedirector */
        $stepRedirector = $this->get('stepRedirector')
            ->setRoutes('odr_assets_type', 'odr_assets_property_step', 'odr_assets_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['odrId' => $odrId, 'assetId' => $assetId]);


        if ($assetId) { // edit asset
            $assets = array_filter($odr->getAssets(), function ($t) use ($assetId) {
                return $t->getId() == $assetId;
            });
            $asset = array_shift($assets);
            $stepRedirector->setFromPage('summary');
        } else { // add new asset
            $asset = new EntityDir\Odr\AssetProperty();
        }

        // add URL-data into model
        isset($dataFromUrl['address']) && $asset->setAddress($dataFromUrl['address']);
        isset($dataFromUrl['address2']) && $asset->setAddress2($dataFromUrl['address']);
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
        $form = $this->createForm(new FormDir\Odr\Asset\AssetTypeProperty($step), $asset);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {

            $asset = $form->getData();
            /* @var $asset Odr\AssetProperty */

            // edit mode: save immediately and go back to summary page
            if ($assetId) {
                $this->getRestClient()->put("odr/{$odrId}/asset/{$assetId}", $asset);
                $request->getSession()->getFlashBag()->add('notice', 'Asset edited');

                return $this->redirect($this->generateUrl('odr_assets_summary', ['odrId' => $odrId]));
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
                $this->getRestClient()->post("odr/{$odrId}/asset", $asset);

                return $this->redirect($this->generateUrl('odr_assets_add_another', ['odrId' => $odrId]));
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'asset' => $asset,
            'odr' => $odr,
            'step' => $step,
            'odrStatus' => new odrStatusService($odr),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
        ];
    }


    /**
     * @Route("/odr/{odrId}/assets/summary", name="odr_assets_summary")
     * @Template()
     *
     * @param int $odrId
     *
     * @return array
     */
    public function summaryAction($odrId)
    {
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        if (count($odr->getAssets()) == 0 && $odr->getNoAssetToAdd() === null) {
            return $this->redirect($this->generateUrl('odr_assets', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/{odrId}/assets/{assetId}/delete", name="asset_delete")
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $odrId, $assetId)
    {
        $odr = $this->getOdr($odrId, self::$jmsGroups);

        if ($odr->hasAssetWithId($assetId)) {
            $this->getRestClient()->delete("/odr/{$odrId}/asset/{$assetId}");
            $request->getSession()->getFlashBag()->add('notice', 'Asset removed');
        }

        return $this->redirect($this->generateUrl('odr_assets', ['odrId' => $odrId]));
    }

//
//    /**
//     * List assets and also handle no-asset checkbox-form.
//     *
//     * @Route("/odr/{odrId}/assets", name="odr_assets")
//     * @Route("/odr/{odrId}/assets", name="odr-assets")
//     * @Template("AppBundle:Odr/Asset:list.html.twig")
//     */
//    public function listAction(Request $request, $odrId)
//    {
//        $odr = $this->getOdr($odrId, self::$jmsGroups);
//        if ($odr->getSubmitted()) {
//            throw new \RuntimeException('Odr already submitted and not editable.');
//        }
//        $assets = $odr->getAssets();
//
//        return [
//            'odr' => $odr,
//            'odr_assets' => $assets,
//        ];
//    }
//
//    /**
//     * Form to select asset title (dropdown only)
//     * when submitted and valid, redirects to 'odr-asset-add-complete'.
//     *
//     * When JS is enabled, there the content of that page is auto-loaded via AJAX
//     *
//     * @Route("/odr/{odrId}/assets/add", name="odr-asset-add-select-title")
//     * @Template("AppBundle:Odr/Asset:addSelectTitle.html.twig")
//     */
//    public function addSelectTitleAction(Request $request, $odrId)
//    {
//        $odr = $this->getOdr($odrId, self::$jmsGroups);
//
//        $form = $this->createForm('odr_asset_title', new EntityDir\Odr\AssetOther(), [
//            'action' => $this->generateUrl('odr-asset-add-select-title', ['odrId' => $odrId]),
//        ]);
//
//        $form->handleRequest($request);
//        if ($form->isValid()) {
//            return $this->redirect($this->generateUrl('odr-asset-add-complete', ['odrId' => $odrId, 'title' => $form->getData()->getTitle()]));
//        }
//
//        return [
//            'odr' => $odr,
//            'form' => $form->createView(),
//            'showCancelLink' => count($odr->getAssets()) > 0,
//        ];
//    }
//
//    /**
//     * Shows the full add asset form.
//     *
//     * @Route("/odr/{odrId}/assets/add-complete/{title}", name="odr-asset-add-complete")
//     * @Template("AppBundle:Odr/Asset:addComplete.html.twig")
//     */
//    public function addCompleteAction(Request $request, $odrId, $title)
//    {
//        $odr = $this->getOdr($odrId, self::$jmsGroups);
//
//        // [.. change form and template (or forward) depending on the asset title ]
//        $asset = EntityDir\Odr\Asset::factory($title);
//
//        $form = $this->createForm(FormDir\Odr\Asset\AbstractAssetType::factory($title), $asset, [
//            'action' => $this->generateUrl('odr-asset-add-complete', ['odrId' => $odrId, 'title' => $title]),
//        ]);
//
//        $form->handleRequest($request);
//
//        // handle submit odr
//        if ($form->isValid()) {
//            $asset = $form->getData();
//            $asset->setOdr($odr);
//            $this->getRestClient()->post("odr/{$odrId}/asset", $asset);
//
//            return $this->redirect($this->generateUrl('odr-assets', ['odrId' => $odrId]));
//        }
//
//        return [
//            'odr' => $odr,
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
//     * @Route("/odr/{odrId}/assets/{assetId}/edit", name="odr-asset-edit")
//     * @Template("AppBundle:Odr/Asset:edit.html.twig")
//     */
//    public function editAction(Request $request, $odrId, $assetId)
//    {
//        $odr = $this->getOdr($odrId, self::$jmsGroups);
//        if (!$odr->hasAssetWithId($assetId)) {
//            throw new \RuntimeException('Asset not found.');
//        }
//        $asset = $this->getRestClient()->get("odr/{$odrId}/asset/{$assetId}", 'Odr\Asset');
//        $form = $this->createForm(FormDir\Odr\Asset\AbstractAssetType::factory($asset->getType()), $asset);
//
//        $form->handleRequest($request);
//
//        // handle submit odr
//        if ($form->isValid()) {
//            $asset = $form->getData();
//            $this->getRestClient()->put("odr/{$odrId}/asset/{$assetId}", $asset);
//
//            return $this->redirect($this->generateUrl('odr-assets', ['odrId' => $odrId]));
//        }
//
//        return [
//            'odr' => $odr,
//            'assetToEdit' => $asset,
//            'form' => $form->createView(),
//        ];
//    }
//
//    /**
//     * @Route("/odr/{odrId}/assets/{id}/delete", name="odr-delete-asset")
//     *
//     * @param int $id
//     *
//     * @return RedirectResponse
//     */
//    public function deleteAction($odrId, $id)
//    {
//        $odr = $this->getOdr($odrId, self::$jmsGroups);
//
//        if ($odr->hasAssetWithId($id)) {
//            $this->getRestClient()->delete("/odr/{$odrId}/asset/{$id}");
//        }
//
//        return $this->redirect($this->generateUrl('odr-assets', ['odrId' => $odrId]));
//    }
//
//    /**
//     * Sub controller action called when the no decision form is embedded in another page.
//     *
//     * @Route("/odr/{odrId}/noassets", name="no_assets")
//     *
//     * @Template("AppBundle:Odr/Asset:_noAssets.html.twig")
//     */
//    public function _noAssetsAction(Request $request, $odrId)
//    {
//        $odr = $this->getOdr($odrId, self::$jmsGroups);
//        $form = $this->createForm(new FormDir\Odr\Asset\NoAssetToAddType(), $odr, []);
//        $form->handleRequest($request);
//
//        if ($request->getMethod() == 'POST') {
//            $this->getRestClient()->put('odr/'.$odrId, $form->getData(), ['noAssetsToAdd']);
//        }
//
//        return [
//            'form' => $form->createView(),
//            'odr' => $odr,
//        ];
//    }
}
