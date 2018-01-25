<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AssetController extends RestController
{
    /**
     * @Route("/ndr/{ndrId}/assets", requirements={"ndrId":"\d+"})
     * @Method({"GET"})
     */
    public function getAll($ndrId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $assets = $this->getRepository(EntityDir\Ndr\Asset::class)->findByNdr($ndr);

        if (count($assets) == 0) {
            return [];
        }

        $this->setJmsSerialiserGroups(['ndr-asset']);

        return $assets;
    }

    /**
     * @Route("/ndr/{ndrId}/asset/{assetId}", requirements={"ndrId":"\d+", "assetId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById($ndrId, $assetId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $asset = $this->findEntityBy(EntityDir\Ndr\Asset::class, $assetId);
        $this->denyAccessIfNdrDoesNotBelongToUser($asset->getNdr());

        $this->setJmsSerialiserGroups(['ndr-asset']);

        return $asset;
    }

    /**
     * @Route("/ndr/{ndrId}/asset", requirements={"ndrId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $ndrId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId); /* @var $ndr EntityDir\Ndr\Ndr */
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);
        $this->validateArray($data, [
            'type' => 'mustExist',
        ]);
        $asset = EntityDir\Ndr\Asset::factory($data['type']);
        $asset->setNdr($ndr);

        $this->updateEntityWithData($asset, $data);
        $ndr->setNoAssetToAdd(false);
        $this->persistAndFlush($asset);

        return ['id' => $asset->getId()];
    }

    /**
     * @Route("/ndr/{ndrId}/asset/{assetId}", requirements={"ndrId":"\d+", "assetId":"\d+"})
     * @Method({"PUT"})
     */
    public function edit(Request $request, $ndrId, $assetId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $asset = $this->findEntityBy(EntityDir\Ndr\Asset::class, $assetId);
        $this->denyAccessIfNdrDoesNotBelongToUser($asset->getNdr());

        $this->updateEntityWithData($asset, $data);

        $this->getEntityManager()->flush($asset);

        return ['id' => $asset->getId()];
    }

    /**
     * @Route("/ndr/{ndrId}/asset/{assetId}", requirements={"ndrId":"\d+", "assetId":"\d+"})
     * @Method({"DELETE"})
     */
    public function delete($ndrId, $assetId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $asset = $this->findEntityBy(EntityDir\Ndr\Asset::class, $assetId);
        $this->denyAccessIfNdrDoesNotBelongToUser($asset->getNdr());

        $this->getEntityManager()->remove($asset);
        $this->getEntityManager()->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Ndr\Asset $asset, array $data)
    {
        // common props
        $this->hydrateEntityWithArrayData($asset, $data, [
            'value' => 'setValue',
        ]);

        if ($asset instanceof EntityDir\Ndr\AssetOther) {
            $this->hydrateEntityWithArrayData($asset, $data, [
                'title' => 'setTitle',
                'description' => 'setDescription',
            ]);

            if (isset($data['valuation_date'])) {
                $asset->setValuationDate(new \DateTime($data['valuation_date']));
            }
        }

        if ($asset instanceof EntityDir\Ndr\AssetProperty) {
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
