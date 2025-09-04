<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\Report\Asset;
use App\Entity\Report\AssetOther;
use App\Entity\Report\AssetProperty;
use App\Entity\Report\Report;
use App\Service\Formatter\RestFormatter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AssetController extends RestController
{
    private array $sectionIds = [Report::SECTION_ASSETS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/asset/{assetId}', requirements: ['reportId' => '\d+', 'assetId' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $reportId, int $assetId): Asset
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $asset = $this->findEntityBy(Asset::class, $assetId);
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['asset'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $asset;
    }

    #[Route(path: '/report/{reportId}/asset', requirements: ['reportId' => '\d+'], methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function add(Request $request, int $reportId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(Report::class, $reportId); /* @var $report \App\Entity\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->formatter->validateArray($data, [
            'type' => 'mustExist',
        ]);
        $asset = Asset::factory($data['type']);
        $asset->setReport($report);
        $report->setNoAssetToAdd(false);

        $this->updateEntityWithData($asset, $data);

        $this->em->persist($asset);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $asset->getId()];
    }

    #[Route(path: '/report/{reportId}/asset/{assetId}', requirements: ['reportId' => '\d+', 'assetId' => '\d+'], methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function edit(Request $request, int $reportId, int $assetId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $asset = $this->findEntityBy(Asset::class, $assetId);
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());

        $this->updateEntityWithData($asset, $data);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $asset->getId()];
    }

    #[Route(path: '/report/{reportId}/asset/{assetId}', requirements: ['reportId' => '\d+', 'assetId' => '\d+'], methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function delete(int $reportId, int $assetId): array
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $asset = $this->findEntityBy(Asset::class, $assetId);
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());

        $this->em->remove($asset);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function updateEntityWithData(Asset $asset, array $data): void
    {
        // common propertie
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
