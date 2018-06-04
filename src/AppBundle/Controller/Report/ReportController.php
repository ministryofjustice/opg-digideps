<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\ReportService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/report")
 */
class ReportController extends RestController
{
    /**
     * Add a report
     * Currently only used by Lay deputy during registration steps
     * Pa report are instead created via OrgService::createReport()
     *
     * @Route("")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAction(Request $request)
    {
        $reportData = $this->deserializeBodyContent($request);

        if (empty($reportData['client']['id'])) {
            throw new \InvalidArgumentException('Missing client.id');
        }
        $client = $this->findEntityBy(EntityDir\Client::class, $reportData['client']['id']);
        $this->denyAccessIfClientDoesNotBelongToUser($client);

        $this->validateArray($reportData, [
            'start_date' => 'notEmpty',
            'end_date'   => 'notEmpty',
        ]);

        // report type is taken from CASREC. In case that's not available (shouldn't happen unless casrec table is dropped), use a 102
        $reportType = $this->get('opg_digideps.report_service')->getReportTypeBasedOnCasrec($client) ?: Report::TYPE_102;
        $report = new Report($client, $reportType, new \DateTime($reportData['start_date']), new \DateTime($reportData['end_date']));
        $report->setReportSeen(true);

        $this->persistAndFlush($report);

        return ['report' => $report->getId()];
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY') or has_role('ROLE_CASE_MANAGER')")
     *
     * @param int $id
     *
     * @return Report
     */
    public function getById(Request $request, $id)
    {
        $groups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['report'];

        $this->setJmsSerialiserGroups($groups);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $id);
        /* @var $report Report */
        if (!$this->isGranted(EntityDir\User::ROLE_CASE_MANAGER)) {
            $this->denyAccessIfReportDoesNotBelongToUser($report);
        }

        return $report;
    }

    /**
     * @Route("/{id}/submit", requirements={"id":"\d+"})
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function submit(Request $request, $id)
    {
        $currentReport = $this->findEntityBy(EntityDir\Report\Report::class, $id, 'Report not found');
        /* @var $currentReport Report */
        $this->denyAccessIfReportDoesNotBelongToUser($currentReport);

        $data = $this->deserializeBodyContent($request);

        if (empty($data['submit_date'])) {
            throw new \InvalidArgumentException('Missing submit_date');
        }

        if (empty($data['agreed_behalf_deputy'])) {
            throw new \InvalidArgumentException('Missing agreed_behalf_deputy');
        }

        $currentReport->setAgreedBehalfDeputy($data['agreed_behalf_deputy']);
        $xplanation = ($data['agreed_behalf_deputy'] === 'more_deputies_not_behalf')
            ? $data['agreed_behalf_deputy_explanation'] : null;
        $currentReport->setAgreedBehalfDeputyExplanation($xplanation);

        // submit and create new year's report
        $nextYearReport = $this->get('opg_digideps.report_service')
            ->submit($currentReport, $this->getUser(), new \DateTime($data['submit_date']));

        //response to pass back. if the report was alreay submitted, no NY report is created
        return $nextYearReport ? $nextYearReport->getId() : null;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function update(Request $request, $id)
    {
        /* @var $report Report */
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $id, 'Report not found');


        // deputies can only edit their own reports
        if (!$this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            $this->denyAccessIfReportDoesNotBelongToUser($report);
        }

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

        if (array_key_exists('debt_management', $data)) {
            $report->setDebtManagement($data['debt_management']);
        }

        if (array_key_exists('fees', $data)) {
            foreach ($data['fees'] as $row) {
                $fee = $report->getFeeByTypeId($row['fee_type_id']);
                if (!$fee instanceof EntityDir\Report\Fee) {
                    continue; //not clear when that might happen. kept similar to transaction below
                }
                $fee->setAmountAndDetails($row['amount'], $row['more_details']);
                $this->getEntityManager()->flush($fee);
            }
        }

        if (array_key_exists('reason_for_no_fees', $data)) {
            $report->setReasonForNoFees($data['reason_for_no_fees']);
            if ($data['reason_for_no_fees']) {
                foreach ($report->getFees() as $fee) {
                    $fee->setAmount(null)
                        ->setMoreDetails(null);
                }
            }
        }

        if (array_key_exists('paid_for_anything', $data)) {
            if ($data['paid_for_anything'] === 'no') { // remove existing expenses
                foreach ($report->getExpenses() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setPaidForAnything($data['paid_for_anything']);
        }

        if (array_key_exists('gifts_exist', $data)) {
            if ($data['gifts_exist'] === 'no') { // remove existing gift
                foreach ($report->getGifts() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setGiftsExist($data['gifts_exist']);
        }

        if (array_key_exists('start_date', $data)) {
            $report->setStartDate(new \DateTime($data['start_date']));
        }

        if (array_key_exists('end_date', $data)) {
            $report->setEndDate(new \DateTime($data['end_date']));
            //end date could be updated automatically with a listener, but better not to overload
            // the default behaviour until the logic is 100% clear
            $report->updateDueDateBasedOnEndDate();
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
            if ($data['no_transfers_to_add'] === true) {
                //true here means "no", so remove existing transfers
                foreach ($report->getMoneyTransfers() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }

            $report->setNoTransfersToAdd($data['no_transfers_to_add']);
        }

        if (array_key_exists('reason_for_no_decisions', $data)) {
            $report->setReasonForNoDecisions($data['reason_for_no_decisions']);
        }

        if (array_key_exists('balance_mismatch_explanation', $data)) {
            $report->setBalanceMismatchExplanation($data['balance_mismatch_explanation']);
        }

        if (array_key_exists('action_more_info', $data)) {
            $report->setActionMoreInfo($data['action_more_info']);
            if (array_key_exists('action_more_info_details', $data)) {
                $report->setActionMoreInfoDetails(
                    $data['action_more_info'] == 'yes' ? $data['action_more_info_details'] : null
                );
            }
        }

        if (array_key_exists('money_short_categories_in', $data)) {
            foreach ($data['money_short_categories_in'] as $row) {
                $e = $report->getMoneyShortCategoryByTypeId($row['type_id']);
                if ($e instanceof EntityDir\Report\MoneyShortCategory) {
                    $e
                        ->setPresent($row['present']);
                    $this->getEntityManager()->flush($e);
                }
            }
        }

        if (array_key_exists('money_short_categories_out', $data)) {
            foreach ($data['money_short_categories_out'] as $row) {
                $e = $report->getMoneyShortCategoryByTypeId($row['type_id']);
                if ($e instanceof EntityDir\Report\MoneyShortCategory) {
                    $e
                        ->setPresent($row['present']);
                    $this->getEntityManager()->flush($e);
                }
            }
        }

        if (array_key_exists('money_transactions_short_in_exist', $data)) {
            if ($data['money_transactions_short_in_exist'] === 'no') { // remove existing
                foreach ($report->getMoneyTransactionsShortIn() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setMoneyTransactionsShortInExist($data['money_transactions_short_in_exist']);
        }

        if (array_key_exists('money_transactions_short_out_exist', $data)) {
            if ($data['money_transactions_short_out_exist'] === 'no') { // remove existing
                foreach ($report->getMoneyTransactionsShortOut() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setMoneyTransactionsShortOutExist($data['money_transactions_short_out_exist']);
        }

        if (array_key_exists('wish_to_provide_documentation', $data)) {
            if ('yes' == $data['wish_to_provide_documentation']
                || ('no' == $data['wish_to_provide_documentation'] && 0 == count($report->getDocuments()))) {
                $report->setWishToProvideDocumentation($data['wish_to_provide_documentation']);
            }
        }


        if (array_key_exists('previous_prof_fees_estimate_given', $data)) {
            $report->setPreviousProfFeesEstimateGiven($data['previous_prof_fees_estimate_given']);
            if ($data['previous_prof_fees_estimate_given'] === 'no') {
                $report->setProfFeesEstimateSccoReason(null);
            } else {
                $report->setProfFeesEstimateSccoReason($data['prof_fees_estimate_scco_reason']);
            }
        }

        if (array_key_exists('current_prof_payments_received', $data)) {
            if ($data['current_prof_payments_received'] =='no') { //reset whole section
                foreach ($report->getCurrentProfServiceFees() as $f) {
                    $this->getEntityManager()->remove($f);
                }
                $report->setPreviousProfFeesEstimateGiven(null);
                $report->setProfFeesEstimateSccoReason(null);
            }
            $report->setCurrentProfPaymentsReceived($data['current_prof_payments_received']);
        }

        $this->getEntityManager()->flush();

        return ['id' => $report->getId()];
    }

    /**
     * @Route("/{id}/unsubmit", requirements={"id":"\d+"})
     * @Method({"PUT"})
     * @Security("has_role('ROLE_CASE_MANAGER')")
     */
    public function unsubmit(Request $request, $id)
    {
        /**
         * @var $report Report
         */
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $id, 'Report not found');
        if (!$report->getSubmitted()) {
            throw new \RuntimeException('Cannot unsubmit an active report');
        }

        $data = $this->deserializeBodyContent($request, [
            'un_submit_date'            => 'notEmpty',
            'due_date'                  => 'notEmpty',
            'unsubmitted_sections_list' => 'notEmpty',
        ]);

        $rs = $this->get('opg_digideps.report_service'); /** @var $rs ReportService */
        $rs->unSubmit(
            $report,
            new \DateTime($data['un_submit_date']),
            new \DateTime($data['due_date']),
            $data['unsubmitted_sections_list']
        );

        $this->getEntityManager()->flush();

        return ['id' => $report->getId()];
    }

    /**
     * Get list of reports, currently only for PA users
     *
     *
     * @Route("/get-all")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function getAll(Request $request)
    {
        $userId = $this->getUser()->getId(); //  take the PA user. Extend/remove when/if needed
        $offset = $request->get('offset');
        $q = $request->get('q');
        $status = $request->get('status');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort');
        $sortDirection = $request->get('sort_direction');

        $qb = $this->getRepository(EntityDir\Report\Report::class)->createQueryBuilder('r');
        $qb
            ->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            ->where('u.id = ' . $userId);

        if ($request->get('exclude_submitted')) {
            $qb->andWhere('r.submitted = false OR r.submitted is null');
        }

        if ($sort == 'end_date') {
            $qb->addOrderBy('r.endDate', strtolower($sortDirection) == 'desc' ? 'DESC' : 'ASC');
            $qb->addOrderBy('c.caseNumber', 'ASC');
        }

        if ($q) {
            $qb->andWhere('lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike OR c.caseNumber = :q');
            $qb->setParameter('qLike', '%' . strtolower($q) . '%');
            $qb->setParameter('q', $q);
        }

        $records = $qb->getQuery()->getResult();
        /* @var $records Report[] */

        // calculate counts, and apply limit/offset
        $counts = ['total'         => 0,
                   'notStarted'    => 0,
                   'notFinished'   => 0,
                   'readyToSubmit' => 0];
        foreach ($records as $report) {
            $counts[$report->getStatus()->getStatus()]++;
            $counts['total']++;
        }

        // status filters
        if ($status) {
            $records = array_filter($records, function ($report) use ($status) {
                return $report->getStatus()->getStatus() == $status;
            });
        }
        // apply offset and limit filters (has to be last)
        $records = array_slice($records, $offset, $limit);

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups')
            : ['report', 'report-client', 'client', 'status'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return [
            'counts'  => $counts,
            'reports' => $records,
        ];
    }

    /**
     * @Route("/{id}/submit-documents", requirements={"id":"\d+"})
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function submitDocuments(Request $request, $id)
    {
        $currentReport = $this->findEntityBy(EntityDir\Report\Report::class, $id, 'Report not found');
        /* @var $currentReport Report */
        $this->denyAccessIfReportDoesNotBelongToUser($currentReport);

        $data = $this->deserializeBodyContent($request);

        // submit and create new year's report
        $report = $this->get('opg_digideps.report_service')
            ->submitAdditionalDocuments($currentReport, $this->getUser(), new \DateTime());

        //response to pass back
        return ['reportId' => $currentReport->getId()];
    }

    /**
     * Add a checklist for the report
     *
     * @Route("/{report_id}/checked", requirements={"report_id":"\d+"})
     * @Method({"POST"})
     * @Security("has_role('ROLE_CASE_MANAGER')")
     */
    public function insertChecklist(Request $request, $report_id)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $report_id, 'Report not found');

        $checklistData = $this->deserializeBodyContent($request);
        $checklist = new EntityDir\Report\Checklist($report);
        $this->hydrateEntityWithArrayData($checklist, $checklistData, [
            'reporting_period_accurate' => 'setReportingPeriodAccurate',
            'contact_details_upto_date' => 'setContactDetailsUptoDate',
            'deputy_full_name_accuratein_casrec' => 'setDeputyFullNameAccurateinCasrec',
            'decisions_satisfactory' => 'setDecisionsSatisfactory',
            'consultations_satisfactory' => 'setConsultationsSatisfactory',
            'care_arrangements' => 'setCareArrangements',
            'assets_declared_and_managed' => 'setAssetsDeclaredAndManaged',
            'debts_managed' => 'setDebtsManaged',
            'open_closing_balances_match' => 'setOpenClosingBalancesMatch',
            'accounts_balance' => 'setAccountsBalance',
            'money_movements_acceptable' => 'setMoneyMovementsAcceptable',
            'bond_adequate' => 'setBondAdequate',
            'bond_order_match_casrec' => 'setBondOrderMatchCasrec',
            'future_significant_financial_decisions' => 'setFutureSignificantFinancialDecisions',
            'has_deputy_raised_concerns' => 'setHasDeputyRaisedConcerns',
            'case_worker_satisified' => 'setCaseWorkerSatisified',
            'lodging_summary' => 'setLodgingSummary',
            'final_decision' => 'setFinalDecision'
        ]);

        if (!empty($checklistData['further_information_received'])) {
            $info = new EntityDir\Report\ChecklistInformation($checklist, $checklistData['further_information_received']);
            $info->setCreatedBy($this->getUser());
            $this->getEntityManager()->persist($info);
        }

        $this->persistAndFlush($checklist);

        return ['checklist' => $checklist->getId()];
    }

    /**
     * Update a checklist for the report
     *
     * @Route("/{report_id}/checked", requirements={"report_id":"\d+"})
     * @Method({"PUT"})
     * @Security("has_role('ROLE_CASE_MANAGER')")
     */
    public function updateChecklist(Request $request, $report_id)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $report_id, 'Report not found');

        $checklistData = $this->deserializeBodyContent($request);

        /** @var EntityDir\Report\Checklist $checklist */
        $checklist = $report->getChecklist();
        $this->hydrateEntityWithArrayData($checklist, $checklistData, [
            'reporting_period_accurate' => 'setReportingPeriodAccurate',
            'contact_details_upto_date' => 'setContactDetailsUptoDate',
            'deputy_full_name_accuratein_casrec' => 'setDeputyFullNameAccurateinCasrec',
            'decisions_satisfactory' => 'setDecisionsSatisfactory',
            'consultations_satisfactory' => 'setConsultationsSatisfactory',
            'care_arrangements' => 'setCareArrangements',
            'assets_declared_and_managed' => 'setAssetsDeclaredAndManaged',
            'debts_managed' => 'setDebtsManaged',
            'open_closing_balances_match' => 'setOpenClosingBalancesMatch',
            'accounts_balance' => 'setAccountsBalance',
            'money_movements_acceptable' => 'setMoneyMovementsAcceptable',
            'bond_adequate' => 'setBondAdequate',
            'bond_order_match_casrec' => 'setBondOrderMatchCasrec',
            'future_significant_financial_decisions' => 'setFutureSignificantFinancialDecisions',
            'has_deputy_raised_concerns' => 'setHasDeputyRaisedConcerns',
            'case_worker_satisified' => 'setCaseWorkerSatisified',
            'lodging_summary' => 'setLodgingSummary',
            'final_decision' => 'setFinalDecision'
        ]);

        if (!empty($checklistData['further_information_received'])) {
            $info = new EntityDir\Report\ChecklistInformation($checklist, $checklistData['further_information_received']);
            $info->setCreatedBy($this->getUser());
            $this->getEntityManager()->persist($info);
        }

        $this->persistAndFlush($checklist);

        return ['checklist' => $checklist->getId()];
    }
}
