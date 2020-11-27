<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class AssetController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_ASSETS];
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/report/{reportId}/asset/{assetId}", requirements={"reportId":"\d+", "assetId":"\d+"}, methods={"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $reportId, $assetId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $asset = $this->findEntityBy(EntityDir\Report\Asset::class, $assetId);
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['asset'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $asset;
    }

    /**
     * @Route("/report/{reportId}/asset", requirements={"reportId":"\d+"}, methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request, $reportId)
    {
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->validateArray($data, [
            'type' => 'mustExist',
        ]);
        $asset = EntityDir\Report\Asset::factory($data['type']);
        $asset->setReport($report);
        $report->setNoAssetToAdd(false);

        $this->updateEntityWithData($asset, $data);

        $this->em->persist($asset);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $asset->getId()];
    }

    /**
     * @Route("/report/{reportId}/asset/{assetId}", requirements={"reportId":"\d+", "assetId":"\d+"}, methods={"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function edit(Request $request, $reportId, $assetId)
    {
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $asset = $this->findEntityBy(EntityDir\Report\Asset::class, $assetId);
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());

        $this->updateEntityWithData($asset, $data);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $asset->getId()];
    }

    /**
     * @Route("/report/{reportId}/asset/{assetId}", requirements={"reportId":"\d+", "assetId":"\d+"}, methods={"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function delete($reportId, $assetId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $asset = $this->findEntityBy(EntityDir\Report\Asset::class, $assetId);
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());

        $this->em->remove($asset);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Report\Asset $asset, array $data)
    {
        // common propertie
        $this->hydrateEntityWithArrayData($asset, $data, [
            'value' => 'setValue',
        ]);

        if ($asset instanceof EntityDir\Report\AssetOther) {
            $this->hydrateEntityWithArrayData($asset, $data, [
                'title' => 'setTitle',
                'description' => 'setDescription',
            ]);

            if (isset($data['valuation_date'])) {
                $asset->setValuationDate(new \DateTime($data['valuation_date']));
            }
        }

        if ($asset instanceof EntityDir\Report\AssetProperty) {
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
