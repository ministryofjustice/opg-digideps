<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends RestController
{
    /**
     * @Route("/report")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $reportData = $this->deserializeBodyContent($request);

        // new report
        if (empty($reportData['client']['id'])) {
            throw new \InvalidArgumentException('Missing client.id');
        }
        /** @var $client EntityDir\Client */
        $client = $this->findEntityBy('Client', $reportData['client']['id']);
        $this->denyAccessIfClientDoesNotBelongToUser($client);

        $report = new Report();
        $report->setClient($client);

        // the below will change when it's decide where COT will be moved
        $courtOrderType = $this->findEntityBy('CourtOrderType', $reportData['court_order_type_id']);
        $report->setCourtOrderType($courtOrderType);
        if ($reportData['court_order_type_id'] == Report::PROPERTY_AND_AFFAIRS) {
            /**
             * Introduced by
             * https://opgtransform.atlassian.net/browse/DDPB-757
             * Remove when
             * https://opgtransform.atlassian.net/browse/DDPB-758
             * is implemented
             */
            if ($this->getUser()->getEmail() == 'laydeputy103@publicguardian.gsi.gov.uk') {
                $report->setType(Report::TYPE_103);
            } else {
                $report->setType(Report::TYPE_102);
            }
        }

        // set report type based on casrec
        $casRec = $this->getRepository('CasRec')->findOneBy(['caseNumber'=>$client->getCaseNumber()]); /* @var $casRec EntityDir\CasRec */
        switch ($casRec ? $casRec->getTypeOfReport() : null) {
            case 'OPG103':
                $report->setType(Report::TYPE_103);
                break;
            case 'OPG102':
            default:
                $report->setType(Report::TYPE_103);
                break;
        }

        $this->validateArray($reportData, [
            'start_date' => 'notEmpty',
            'end_date' => 'notEmpty',
        ]);

        // add other stuff
        $report->setStartDate(new \DateTime($reportData['start_date']));
        $report->setEndDate(new \DateTime($reportData['end_date']));
        $report->setReportSeen(true);

        $this->persistAndFlush($report);

        return ['report' => $report->getId()];
    }

    /**
     * @Route("/report/{id}")
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function getById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $groups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['report'];
        $this->setJmsSerialiserGroups($groups);

        $report = $this->findEntityBy('Report\Report', $id);
        /* @var $report Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        return $report;
    }

    /**
     * @Route("/report/{id}/submit")
     * @Method({"PUT"})
     */
    public function submit(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $currentReport = $this->findEntityBy('Report\Report', $id, 'Report not found');
        /* @var $currentReport Report */
        $this->denyAccessIfReportDoesNotBelongToUser($currentReport);
        $user = $this->getUser();
        $client = $currentReport->getClient();

        $data = $this->deserializeBodyContent($request);

        if (empty($data['submit_date'])) {
            throw new \InvalidArgumentException('Missing submit_date');
        }

        if (empty($data['agreed_behalf_deputy'])) {
            throw new \InvalidArgumentException('Missing agreed_behalf_deputy');
        }

        $currentReport->setAgreedBehalfDeputy($data['agreed_behalf_deputy']);
        if ($data['agreed_behalf_deputy'] === 'more_deputies_not_behalf') {
            $currentReport->setAgreedBehalfDeputyExplanation($data['agreed_behalf_deputy_explanation']);
        } else {
            $currentReport->setAgreedBehalfDeputyExplanation(null);
        }

        $currentReport->setSubmitted(true);
        $currentReport->setSubmitDate(new \DateTime($data['submit_date']));

        //lets create subsequent year's report
        $nextYearReport = $this->getRepository('Report\Report')->createNextYearReport($currentReport);
        $this->getEntityManager()->flush($currentReport);

        //response to pass back
        return ['newReportId' => $nextYearReport->getId()];
    }

    /**
     * @Route("/report/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $id, 'Report not found');
        /* @var $report Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request);

        if (!empty($data['type'])) {
            $report->setType($data['type']);
        }

        if (array_key_exists('has_debts', $data) && in_array($data['has_debts'], ['yes', 'no'])) {
            $report->setHasDebts($data['has_debts']);
            // null debts
            foreach ($report->getDebts() as $debt) {
                $debt->setAmount(null);
                $debt->setMoreDetails(null);
                $this->getEntityManager()->flush($debt);
            }
            // set debts as per "debts" key
            if ($data['has_debts'] == 'yes') {
                foreach ($data['debts'] as $row) {
                    $debt = $report->getDebtByTypeId($row['debt_type_id']);
                    if (!$debt instanceof EntityDir\Report\Debt) {
                        continue; //not clear when that might happen. kept similar to transaction below
                    }
                    $debt->setAmountAndDetails($row['amount'], $row['more_details']);
                    $this->getEntityManager()->flush($debt);
                }
            }
            $this->setJmsSerialiserGroups(['debts']); //returns saved data (AJAX operations)
        }

        if (array_key_exists('paid_for_anything', $data)) {
            $report->setPaidForAnything($data['paid_for_anything']);
            if ($report->getPaidForAnything() === 'no') { // remove existing expenses
                foreach ($report->getExpenses() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
        }

        if (array_key_exists('gifts_exist', $data)) {
            $report->setGiftsExist($data['gifts_exist']);
            if ($report->getGiftsExist() === 'no') { // remove existing gift
                foreach ($report->getGifts() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
        }

        if (array_key_exists('start_date', $data)) {
            $report->setStartDate(new \DateTime($data['start_date']));
        }

        if (array_key_exists('end_date', $data)) {
            $report->setEndDate(new \DateTime($data['end_date']));
        }

        if (array_key_exists('report_seen', $data)) {
            $report->setReportSeen((boolean) $data['report_seen']);
        }

        if (array_key_exists('reason_for_no_contacts', $data)) {
            $report->setReasonForNoContacts($data['reason_for_no_contacts']);
        }

        if (array_key_exists('no_asset_to_add', $data)) {
            $report->setNoAssetToAdd($data['no_asset_to_add']);
            if ($report->getNoAssetToAdd()) {
                foreach ($report->getAssets() as $asset) {
                    $this->getEntityManager()->remove($asset);
                }
                $this->getEntityManager()->flush();
            }
        }

        if (array_key_exists('no_transfers_to_add', $data)) {
            $report->setNoTransfersToAdd($data['no_transfers_to_add']);
        }

        if (array_key_exists('reason_for_no_decisions', $data)) {
            $report->setReasonForNoDecisions($data['reason_for_no_decisions']);
        }

        if (array_key_exists('further_information', $data)) {
            $report->setFurtherInformation($data['further_information']);
        }

        if (array_key_exists('balance_mismatch_explanation', $data)) {
            $report->setBalanceMismatchExplanation($data['balance_mismatch_explanation']);
        }

        if (array_key_exists('metadata', $data)) {
            $report->setMetadata($data['metadata']);
        }

        if (array_key_exists('action_more_info', $data)) {
            $report->setActionMoreInfo($data['action_more_info']);
            if (array_key_exists('action_more_info_details', $data)) {
                $report->setActionMoreInfoDetails(
                    $data['action_more_info'] == 'yes' ? $data['action_more_info_details'] : null
                );
            }
        }

        $this->getEntityManager()->flush();

        return ['id' => $report->getId()];
    }
}
