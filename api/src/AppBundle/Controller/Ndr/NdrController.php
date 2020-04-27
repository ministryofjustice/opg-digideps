<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Document;
use AppBundle\Service\ReportService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class NdrController extends RestController
{
    /**
     * @Route("/ndr/{id}", methods={"GET"})
     *
     * @param int $id
     */
    public function getById(Request $request, $id)
    {
        $groups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['ndr'];
        $this->setJmsSerialiserGroups($groups);

        /* @var $report EntityDir\Ndr\Ndr */
        $report = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $id);

        if (!$this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);
            $this->denyAccessIfNdrDoesNotBelongToUser($report);
        }

        return $report;
    }

    /**
     * @Route("/ndr/{id}/submit", methods={"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function submit(Request $request, $id, ReportService $reportService)
    {
        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $id, 'Ndr not found');
        /* @var $ndr EntityDir\Ndr\Ndr */
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $data = $this->deserializeBodyContent($request);

        if (empty($data['agreed_behalf_deputy'])) {
            throw new \InvalidArgumentException('Missing agreed_behalf_deputy');
        }

        $documentId = $request->get('documentId');
        if (empty($documentId)) {
            throw new \InvalidArgumentException('documentId must be specified');
        }

        /** @var Document $reportPdf */
        $reportPdf = $this->getEntityManager()->getRepository(EntityDir\Report\Document::class)->find($documentId);
        $reportPdf->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
        $reportPdf->setSynchronisedBy($this->getUser());

        $this->getEntityManager()->flush($reportPdf);

        $ndr->setAgreedBehalfDeputy($data['agreed_behalf_deputy']);

        if ($data['agreed_behalf_deputy'] === 'more_deputies_not_behalf') {
            $ndr->setAgreedBehalfDeputyExplanation($data['agreed_behalf_deputy_explanation']);
        } else {
            $ndr->setAgreedBehalfDeputyExplanation(null);
        }

        $ndr->setSubmitted(true);
        $ndr->setSubmitDate(new \DateTime($data['submit_date']));

        // submit and create new year's report
        $nextYearReport = $reportService
            ->submit($ndr, $this->getUser(), new \DateTime($data['submit_date']), $documentId);

        return ['id' => $nextYearReport->getId()];
    }

    /**
     * @Route("/ndr/{id}", methods={"PUT"})
     */
    public function update(Request $request, $id)
    {
        /* @var $ndr EntityDir\Ndr\Ndr */
        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $id, 'Ndr not found');

        if (!$this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);
            $this->denyAccessIfNdrDoesNotBelongToUser($ndr);
        }

        $data = $this->deserializeBodyContent($request);

        if (array_key_exists('has_debts', $data) && in_array($data['has_debts'], ['yes', 'no'])) {
            $ndr->setHasDebts($data['has_debts']);
            // null debts
            foreach ($ndr->getDebts() as $debt) {
                $debt->setAmount(null);
                $debt->setMoreDetails(null);
                $this->getEntityManager()->flush($debt);
            }
            // set debts as per "debts" key
            if ($data['has_debts'] == 'yes') {
                foreach ($data['debts'] as $row) {
                    $debt = $ndr->getDebtByTypeId($row['debt_type_id']);
                    if (!$debt instanceof EntityDir\Ndr\Debt) {
                        continue; //not clear when that might happen. kept similar to transaction below
                    }
                    $debt->setAmountAndDetails($row['amount'], $row['more_details']);
                    $this->getEntityManager()->flush($debt);
                    $this->setJmsSerialiserGroups(['debts']); //returns saved data (AJAX operations)
                }
            }
        }

        if (array_key_exists('debt_management', $data)) {
            $ndr->setDebtManagement($data['debt_management']);
        }

        if (array_key_exists('state_benefits', $data)) {
            foreach ($data['state_benefits'] as $row) {
                $e = $ndr->getStateBenefitByTypeId($row['type_id']);
                if ($e instanceof EntityDir\Ndr\StateBenefit) {
                    $e
                        ->setPresent($row['present'])
                        ->setMoreDetails($row['present'] ? $row['more_details'] : null);
                    $this->getEntityManager()->flush($e);
                }
            }
        }

        if (array_key_exists('receive_state_pension', $data)) {
            $ndr->setReceiveStatePension($data['receive_state_pension']);
        }

        if (array_key_exists('receive_other_income_details', $data)) {
            $ndr->setReceiveOtherIncomeDetails($data['receive_other_income_details']);
        }

        if (array_key_exists('receive_other_income', $data)) {
            if ($data['receive_other_income'] == 'no') {
                $ndr->setReceiveOtherIncomeDetails(null);
            }
            $ndr->setReceiveOtherIncome($data['receive_other_income']);
        }

        if (array_key_exists('expect_compensation_damages_details', $data)) {
            $ndr->setExpectCompensationDamagesDetails($data['expect_compensation_damages_details']);
        }

        if (array_key_exists('expect_compensation_damages', $data)) {
            if ($data['expect_compensation_damages'] == 'no') {
                $ndr->setExpectCompensationDamagesDetails(null);
            }
            $ndr->setExpectCompensationDamages($data['expect_compensation_damages']);
        }

        if (array_key_exists('one_off', $data)) {
            foreach ($data['one_off'] as $row) {
                $e = $ndr->getOneOffByTypeId($row['type_id']);
                if ($e instanceof EntityDir\Ndr\OneOff) {
                    $e->setPresent($row['present'])->setMoreDetails($row['more_details']);
                    $this->getEntityManager()->flush($e);
                }
            }
        }

        if (array_key_exists('no_asset_to_add', $data)) {
            $ndr->setNoAssetToAdd($data['no_asset_to_add']);
            if ($ndr->getNoAssetToAdd()) {
                foreach ($ndr->getAssets() as $asset) {
                    $this->getEntityManager()->remove($asset);
                }
                $this->getEntityManager()->flush();
            }
        }

        if (array_key_exists('paid_for_anything', $data)) {
            if ($data['paid_for_anything'] === 'no') { // remove existing expenses
                foreach ($ndr->getExpenses() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $ndr->setPaidForAnything($data['paid_for_anything']);
        }

        // actions
        if (array_key_exists('action_give_gifts_to_client', $data)) {
            $ndr->setActionGiveGiftsToClient($data['action_give_gifts_to_client']);
            if (array_key_exists('action_give_gifts_to_client_details', $data)) {
                $ndr->setActionGiveGiftsToClientDetails(
                    $data['action_give_gifts_to_client'] == 'yes' ? $data['action_give_gifts_to_client_details'] : null
                );
            }
        }

        if (array_key_exists('action_property_maintenance', $data)) {
            $ndr->setActionPropertyMaintenance($data['action_property_maintenance']);
        }

        if (array_key_exists('action_property_selling_rent', $data)) {
            $ndr->setActionPropertySellingRent($data['action_property_selling_rent']);
        }

        if (array_key_exists('action_property_buy', $data)) {
            $ndr->setActionPropertyBuy($data['action_property_buy']);
        }

        if (array_key_exists('action_more_info', $data)) {
            $ndr->setActionMoreInfo($data['action_more_info']);
            if (array_key_exists('action_more_info_details', $data)) {
                $ndr->setActionMoreInfoDetails(
                    $data['action_more_info'] == 'yes' ? $data['action_more_info_details'] : null
                );
            }
        }

        if (array_key_exists('start_date', $data)) {
            $ndr->setStartDate(new \DateTime($data['start_date']));
        }

        $this->getEntityManager()->flush();

        return ['id' => $ndr->getId()];
    }
}
