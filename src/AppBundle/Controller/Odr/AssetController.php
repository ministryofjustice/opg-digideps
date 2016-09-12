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
    private static $odrJmsGroups = ['odr', 'client', 'odr-asset'];

    /**
     * List assets and also handle no-asset checkbox-form.
     *
     * @Route("/odr/{odrId}/assets", name="odr-assets")
     * @Template("AppBundle:Odr/Asset:list.html.twig")
     */
    public function listAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        $assets = $odr->getAssets();

        return [
            'odr' => $odr,
            'assets' => $assets,
        ];
    }

    /**
     * Form to select asset title (dropdown only)
     * when submitted and valid, redirects to 'odr-asset-add-complete'.
     *
     * When JS is enabled, there the content of that page is auto-loaded via AJAX
     *
     * @Route("/odr/{odrId}/assets/add", name="odr-asset-add-select-title")
     * @Template("AppBundle:Odr/Asset:addSelectTitle.html.twig")
     */
    public function addSelectTitleAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);

        $form = $this->createForm('odr_asset_title', new EntityDir\Odr\AssetOther(), [
            'action' => $this->generateUrl('odr-asset-add-select-title', ['odrId' => $odrId]),
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->redirect($this->generateUrl('odr-asset-add-complete', ['odrId' => $odrId, 'title' => $form->getData()->getTitle()]));
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
            'showCancelLink' => count($odr->getAssets()) > 0,
        ];
    }

    /**
     * Shows the full add asset form.
     *
     * @Route("/odr/{odrId}/assets/add-complete/{title}", name="odr-asset-add-complete")
     * @Template("AppBundle:Odr/Asset:addComplete.html.twig")
     */
    public function addCompleteAction(Request $request, $odrId, $title)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);

        // [.. change form and template (or forward) depending on the asset title ]
        $asset = EntityDir\Odr\Asset::factory($title);

        $form = $this->createForm(FormDir\Odr\Asset\AbstractAssetType::factory($title), $asset, [
            'action' => $this->generateUrl('odr-asset-add-complete', ['odrId' => $odrId, 'title' => $title]),
        ]);

        $form->handleRequest($request);

        // handle submit odr
        if ($form->isValid()) {
            $asset = $form->getData();
            $asset->setOdr($odr);
            $this->getRestClient()->post("odr/{$odrId}/asset", $asset);

            return $this->redirect($this->generateUrl('odr-assets', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
            'asset' => $asset,
            'titleLcFirst' => lcfirst($title),
        ];
    }

    /**
     * Edit a record
     * the edit form is "inline" so it needs.
     *
     * @Route("/odr/{odrId}/assets/{assetId}/edit", name="odr-asset-edit")
     * @Template("AppBundle:Odr/Asset:edit.html.twig")
     */
    public function editAction(Request $request, $odrId, $assetId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if (!$odr->hasAssetWithId($assetId)) {
            throw new \RuntimeException('Asset not found.');
        }
        $asset = $this->getRestClient()->get("odr/{$odrId}/asset/{$assetId}", 'Odr\Asset');
        $form = $this->createForm(FormDir\Odr\Asset\AbstractAssetType::factory($asset->getType()), $asset);

        $form->handleRequest($request);

        // handle submit odr
        if ($form->isValid()) {
            $asset = $form->getData();
            $this->getRestClient()->put("odr/{$odrId}/asset/{$assetId}", $asset);

            return $this->redirect($this->generateUrl('odr-assets', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'assetToEdit' => $asset,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/odr/{odrId}/assets/{id}/delete", name="odr-delete-asset")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction($odrId, $id)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);

        if ($odr->hasAssetWithId($id)) {
            $this->getRestClient()->delete("/odr/{$odrId}/asset/{$id}");
        }

        return $this->redirect($this->generateUrl('odr-assets', ['odrId' => $odrId]));
    }

    /**
     * Sub controller action called when the no decision form is embedded in another page.
     *
     * @Route("/odr/{odrId}/noassets", name="no_assets")
     *
     * @Template("AppBundle:Odr/Asset:_noAssets.html.twig")
     */
    public function _noAssetsAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        $form = $this->createForm(new FormDir\Odr\Asset\NoAssetToAddType(), $odr, []);
        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
            $this->getRestClient()->put('odr/'.$odrId, $form->getData(), ['noAssetsToAdd']);
        }

        return [
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }
}
