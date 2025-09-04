<?php

namespace App\Controller\Ndr;

use App\Controller\RestController;
use App\Entity\Ndr\Asset;
use App\Entity\Ndr\AssetOther;
use App\Entity\Ndr\AssetProperty;
use App\Entity\Ndr\Ndr;
use App\Service\Formatter\RestFormatter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AssetController extends RestController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/ndr/{ndrId}/asset/{assetId}', requirements: ['ndrId' => '\d+', 'assetId' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(int $ndrId, int $assetId): Asset
    {
        $ndr = $this->findEntityBy(Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $asset = $this->findEntityBy(Asset::class, $assetId);
        $this->denyAccessIfNdrDoesNotBelongToUser($asset->getNdr());

        $this->formatter->setJmsSerialiserGroups(['ndr-asset']);

        return $asset;
    }

    #[Route(path: '/ndr/{ndrId}/asset', requirements: ['ndrId' => '\d+'], methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function add(Request $request, int $ndrId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $ndr = $this->findEntityBy(Ndr::class, $ndrId); /* @var $ndr \App\Entity\Ndr\Ndr */
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);
        $this->formatter->validateArray($data, [
            'type' => 'mustExist',
        ]);
        $asset = Asset::factory($data['type']);
        $asset->setNdr($ndr);

        $this->updateEntityWithData($asset, $data);
        $ndr->setNoAssetToAdd(false);

        $this->em->persist($asset);
        $this->em->flush();

        return ['id' => $asset->getId()];
    }

    #[Route(path: '/ndr/{ndrId}/asset/{assetId}', requirements: ['ndrId' => '\d+', 'assetId' => '\d+'], methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function edit(Request $request, int $ndrId, int $assetId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $ndr = $this->findEntityBy(Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $asset = $this->findEntityBy(Asset::class, $assetId);
        $this->denyAccessIfNdrDoesNotBelongToUser($asset->getNdr());

        $this->updateEntityWithData($asset, $data);

        $this->em->flush($asset);

        return ['id' => $asset->getId()];
    }

    #[Route(path: '/ndr/{ndrId}/asset/{assetId}', requirements: ['ndrId' => '\d+', 'assetId' => '\d+'], methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function delete(int $ndrId, int $assetId): array
    {
        $ndr = $this->findEntityBy(Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $asset = $this->findEntityBy(Asset::class, $assetId);
        $this->denyAccessIfNdrDoesNotBelongToUser($asset->getNdr());

        $this->em->remove($asset);
        $this->em->flush();

        return [];
    }

    private function updateEntityWithData(Asset $asset, array $data): void
    {
        // common props
        $this->hydrateEntityWithArrayData($asset, $data, [
            'value' => 'setValue',
        ]);

        if ($asset instanceof AssetOther) {
            $this->hydrateEntityWithArrayData($asset, $data, [
                'title' => 'setTitle',
                'description' => 'setDescription',
            ]);

            if (isset($data['valuation_date'])) {
                $asset->setValuationDate(new DateTime($data['valuation_date']));
            }
        }

        if ($asset instanceof AssetProperty) {
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
                $asset->setRentAgreementEndDate(new DateTime($value));
            }
        }
    }
}
