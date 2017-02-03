<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AssetController extends RestController
{
    /**
     * @Route("/odr/{odrId}/assets", requirements={"odrId":"\d+"})
     * @Method({"GET"})
     */
    public function getAll($odrId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy(EntityDir\Odr\Odr::class, $odrId);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $assets = $this->getRepository(EntityDir\Odr\Asset::class)->findByOdr($odr);

        if (count($assets) == 0) {
            return [];
        }

        return $assets;
    }

    /**
     * @Route("/odr/{odrId}/asset/{assetId}", requirements={"odrId":"\d+", "assetId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById($odrId, $assetId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy(EntityDir\Odr\Odr::class, $odrId);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $asset = $this->findEntityBy(EntityDir\Odr\Asset::class, $assetId);
        $this->denyAccessIfOdrDoesNotBelongToUser($asset->getOdr());

        return $asset;
    }

    /**
     * @Route("/odr/{odrId}/asset", requirements={"odrId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $odrId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $odr = $this->findEntityBy(EntityDir\Odr\Odr::class, $odrId); /* @var $odr EntityDir\Odr\Odr */
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);
        $this->validateArray($data, [
            'type' => 'mustExist',
        ]);
        $asset = EntityDir\Odr\Asset::factory($data['type']);
        $asset->setOdr($odr);

        $this->updateEntityWithData($asset, $data);
        $odr->setNoAssetToAdd(null);
        $this->persistAndFlush($asset);

        return ['id' => $asset->getId()];
    }

    /**
     * @Route("/odr/{odrId}/asset/{assetId}", requirements={"odrId":"\d+", "assetId":"\d+"})
     * @Method({"PUT"})
     */
    public function edit(Request $request, $odrId, $assetId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $odr = $this->findEntityBy(EntityDir\Odr\Odr::class, $odrId);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $asset = $this->findEntityBy(EntityDir\Odr\Asset::class, $assetId);
        $this->denyAccessIfOdrDoesNotBelongToUser($asset->getOdr());

        $this->updateEntityWithData($asset, $data);

        $this->getEntityManager()->flush($asset);

        return ['id' => $asset->getId()];
    }

    /**
     * @Route("/odr/{odrId}/asset/{assetId}", requirements={"odrId":"\d+", "assetId":"\d+"})
     * @Method({"DELETE"})
     */
    public function delete($odrId, $assetId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy(EntityDir\Odr\Odr::class, $odrId);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $asset = $this->findEntityBy(EntityDir\Odr\Asset::class, $assetId);
        $this->denyAccessIfOdrDoesNotBelongToUser($asset->getOdr());

        $this->getEntityManager()->remove($asset);
        $this->getEntityManager()->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Odr\Asset $asset, array $data)
    {
        // common props
        $this->hydrateEntityWithArrayData($asset, $data, [
            'value' => 'setValue',
        ]);

        if ($asset instanceof EntityDir\Odr\AssetOther) {
            $this->hydrateEntityWithArrayData($asset, $data, [
                'title' => 'setTitle',
                'description' => 'setDescription',
            ]);

            if (isset($data['valuation_date'])) {
                $asset->setValuationDate(new \DateTime($data['valuation_date']));
            }
        }

        if ($asset instanceof EntityDir\Odr\AssetProperty) {
            $this->hydrateEntityWithArrayData($asset, $data, [
                'address' => 'setAddress',
                'address2' => 'setAddress2',
                'county' => 'setCounty',
                'postcode' => 'setPostCode',
                'occupants' => 'setOccupants',
                'owned' => 'setOwned',
                'owned_percentage' => 'setOwnedPercentage',
                'is_subject_to_equity_release' => 'setIsSubjectToEquityRelease',
                'has_mortgage' => 'setHasMortgage',
                'mortgage_outstanding_amount' => 'setMortgageOutstandingAmount',
                'has_charges' => 'setHasCharges',
                'is_rented_out' => 'setIsRentedOut',
                'rent_income_month' => 'setRentIncomeMonth',
            ]);

            if (isset($data['rent_agreement_end_date'])) {
                $value = isset($data['rent_agreement_end_date']['date'])
                    ? $data['rent_agreement_end_date']['date'] : $data['rent_agreement_end_date'];
                $asset->setRentAgreementEndDate(new \DateTime($value));
            }
        }
    }
}
