<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\ReportService;
use AppBundle\Service\RestHandler\Report\DeputyCostsEstimateReportUpdateHandler;
use Doctrine\ORM\AbstractQuery;
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
    /** @var array */
    private $updateHandlers;

    public function __construct(array $updateHandlers)
    {
        $this->updateHandlers = $updateHandlers;
    }

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
            'end_date' => 'notEmpty',
        ]);

        // report type is taken from CASREC. In case that's not available (shouldn't happen unless casrec table is dropped), use a 102
        $reportType = $this->get('opg_digideps.report_service')->getReportTypeBasedOnCasrec($client) ?: Report::TYPE_102;
        $report = new Report($client, $reportType, new \DateTime($reportData['start_date']), new \DateTime($reportData['end_date']));
        $report->setReportSeen(true);

        $report->updateSectionsStatusCache($report->getAvailableSections());
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
            ? (array)$request->query->get('groups') : ['report'];

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
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DEBTS
            ]);
        }

        if (array_key_exists('prof_deputy_other_costs', $data)) {
            $defaultCostTypeIds = array_column($report->getProfDeputyOtherCostTypeIds(), 'typeId');

            foreach ($data['prof_deputy_other_costs'] as $postedProfDeputyOtherCostType) {
                if (in_array(
                    $postedProfDeputyOtherCostType['prof_deputy_other_cost_type_id'],
                        $defaultCostTypeIds
                    )) {
                    $profDeputyOtherCost = $report->getProfDeputyOtherCostByTypeId(
                        $postedProfDeputyOtherCostType['prof_deputy_other_cost_type_id']
                    );

                    // update if exists, or instantiate a new entitys
                    if ($profDeputyOtherCost instanceof EntityDir\Report\ProfDeputyOtherCost) {
                        $profDeputyOtherCost->setAmount($postedProfDeputyOtherCostType['amount']);
                    } else {
                        $profDeputyOtherCost = new EntityDir\Report\ProfDeputyOtherCost(
                            $report,
                            $postedProfDeputyOtherCostType['prof_deputy_other_cost_type_id'],
                            $postedProfDeputyOtherCostType['has_more_details'],
                            $postedProfDeputyOtherCostType['amount']
                        );
                    }
                    if ($profDeputyOtherCost->getHasMoreDetails()) {
                        $profDeputyOtherCost->setMoreDetails($postedProfDeputyOtherCostType['more_details']);
                    }

                    $this->getEntityManager()->persist($profDeputyOtherCost);
                }
            }
            $report->updateSectionsStatusCache([
                Report::SECTION_PROF_DEPUTY_COSTS
            ]);
            $this->getEntityManager()->flush();
        }

        if (array_key_exists('debt_management', $data)) {
            $report->setDebtManagement($data['debt_management']);
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DEBTS,
            ]);
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
            $report->updateSectionsStatusCache([
                Report::SECTION_DEPUTY_EXPENSES,
                Report::SECTION_PA_DEPUTY_EXPENSES
            ]);
        }

        if (array_key_exists('reason_for_no_fees', $data)) {
            $report->setReasonForNoFees($data['reason_for_no_fees']);
            if ($data['reason_for_no_fees']) {
                foreach ($report->getFees() as $fee) {
                    $fee->setAmount(null)
                        ->setMoreDetails(null);
                }
            }
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DEPUTY_EXPENSES,
                Report::SECTION_PA_DEPUTY_EXPENSES
            ]);
        }

        if (array_key_exists('paid_for_anything', $data)) {
            if ($data['paid_for_anything'] === 'no') { // remove existing expenses
                foreach ($report->getExpenses() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setPaidForAnything($data['paid_for_anything']);
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DEPUTY_EXPENSES,
                Report::SECTION_PA_DEPUTY_EXPENSES
            ]);
        }

        if (array_key_exists('gifts_exist', $data)) {
            if ($data['gifts_exist'] === 'no') { // remove existing gift
                foreach ($report->getGifts() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setGiftsExist($data['gifts_exist']);
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_GIFTS,
            ]);
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
            $report->setReportSeen((boolean)$data['report_seen']);
        }

        if (array_key_exists('reason_for_no_contacts', $data)) {
            $report->setReasonForNoContacts($data['reason_for_no_contacts']);
            $report->updateSectionsStatusCache([
                Report::SECTION_CONTACTS,
            ]);
        }


        if (array_key_exists('no_asset_to_add', $data)) {
            $report->setNoAssetToAdd($data['no_asset_to_add']);
            if ($report->getNoAssetToAdd()) {
                foreach ($report->getAssets() as $asset) {
                    $this->getEntityManager()->remove($asset);
                }
                $this->getEntityManager()->flush();
            }
            $report->updateSectionsStatusCache([
                Report::SECTION_ASSETS,
            ]);
        }

        if (array_key_exists('no_transfers_to_add', $data)) {
            if ($data['no_transfers_to_add'] === true) {
                //true here means "no", so remove existing transfers
                foreach ($report->getMoneyTransfers() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setNoTransfersToAdd($data['no_transfers_to_add']);
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_TRANSFERS,
            ]);
        }

        if (array_key_exists('reason_for_no_decisions', $data)) {
            $report->setReasonForNoDecisions($data['reason_for_no_decisions']);
            $report->updateSectionsStatusCache([
                Report::SECTION_DECISIONS,
            ]);
        }

        if (array_key_exists('balance_mismatch_explanation', $data)) {
            $report->setBalanceMismatchExplanation($data['balance_mismatch_explanation']);
            $report->updateSectionsStatusCache([
                Report::SECTION_BALANCE,
            ]);
        }

        if (array_key_exists('action_more_info', $data)) {
            $report->setActionMoreInfo($data['action_more_info']);
            if (array_key_exists('action_more_info_details', $data)) {
                $report->setActionMoreInfoDetails(
                    $data['action_more_info'] == 'yes' ? $data['action_more_info_details'] : null
                );
            }
            $report->updateSectionsStatusCache([
                Report::SECTION_OTHER_INFO,
            ]);
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
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_IN_SHORT,
                Report::SECTION_MONEY_OUT_SHORT,
            ]);
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
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_IN_SHORT,
                Report::SECTION_MONEY_OUT_SHORT,
            ]);
        }

        if (array_key_exists('money_transactions_short_in_exist', $data)) {
            if ($data['money_transactions_short_in_exist'] === 'no') { // remove existing
                foreach ($report->getMoneyTransactionsShortIn() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setMoneyTransactionsShortInExist($data['money_transactions_short_in_exist']);
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_IN_SHORT,
                Report::SECTION_MONEY_OUT_SHORT,
            ]);
        }

        if (array_key_exists('money_transactions_short_out_exist', $data)) {
            if ($data['money_transactions_short_out_exist'] === 'no') { // remove existing
                foreach ($report->getMoneyTransactionsShortOut() as $e) {
                    $this->getEntityManager()->remove($e);
                }
            }
            $report->setMoneyTransactionsShortOutExist($data['money_transactions_short_out_exist']);
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_IN_SHORT,
                Report::SECTION_MONEY_OUT_SHORT,
            ]);
        }

        if (array_key_exists('wish_to_provide_documentation', $data)) {
            $report->setWishToProvideDocumentation($data['wish_to_provide_documentation']);
            if ('no' === $data['wish_to_provide_documentation']) {
                $report->setWishToProvideDocumentation($data['wish_to_provide_documentation']);
            }
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DOCUMENTS,
            ]);
        }


        if (array_key_exists('previous_prof_fees_estimate_given', $data)) {
            $report->setPreviousProfFeesEstimateGiven($data['previous_prof_fees_estimate_given']);
            if ($data['previous_prof_fees_estimate_given'] === 'no') {
                $report->setProfFeesEstimateSccoReason(null);
            } else {
                $report->setProfFeesEstimateSccoReason($data['prof_fees_estimate_scco_reason']);
            }
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_PROF_CURRENT_FEES,
            ]);
        }

        if (array_key_exists('current_prof_payments_received', $data)) {
            if ($data['current_prof_payments_received'] == 'no') { //reset whole section
                foreach ($report->getCurrentProfServiceFees() as $f) {
                    $this->getEntityManager()->remove($f);
                }
                $report->setPreviousProfFeesEstimateGiven(null);
                $report->setProfFeesEstimateSccoReason(null);
            }
            $report->setCurrentProfPaymentsReceived($data['current_prof_payments_received']);
            $this->getEntityManager()->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_PROF_CURRENT_FEES,
            ]);
        }

        if (array_key_exists('prof_deputy_costs_how_charged_fixed', $data)) {
            $report->setProfDeputyCostsHowChargedFixed($data['prof_deputy_costs_how_charged_fixed']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        if (array_key_exists('prof_deputy_costs_how_charged_assessed', $data)) {
            $report->setProfDeputyCostsHowChargedAssessed($data['prof_deputy_costs_how_charged_assessed']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        if (array_key_exists('prof_deputy_costs_how_charged_agreed', $data)) {
            $report->setProfDeputyCostsHowChargedAgreed($data['prof_deputy_costs_how_charged_agreed']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        // update depending data depending on the selection on the "how charged" checkboxes
        if (array_key_exists('prof_deputy_costs_how_charged_fixed', $data)
            || array_key_exists('prof_deputy_costs_how_charged_assessed', $data)
            || array_key_exists('prof_deputy_costs_how_charged_agreed', $data)
        ) {
            if ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
                $report->setProfDeputyCostsHasInterim(null);
                foreach ($report->getProfDeputyInterimCosts() as $ic) {
                    $this->getEntityManager()->remove($ic);
                }
            } else if ($report->getProfDeputyCostsHasInterim() === 'yes') {
                $report->setProfDeputyFixedCost(null);
            }
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        if (!empty($data['prof_deputy_costs_has_previous']) && $data['prof_deputy_costs_has_previous']) {
            $report->setProfDeputyCostsHasPrevious($data['prof_deputy_costs_has_previous']);
            foreach ($report->getProfDeputyPreviousCosts() as $pc) {
                $this->getEntityManager()->remove($pc);
            }
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        if (!empty($data['prof_deputy_costs_has_interim']) && $data['prof_deputy_costs_has_interim']) {
            $report->setProfDeputyCostsHasInterim($data['prof_deputy_costs_has_interim']);
            // remove interim if changed to "no"
            if ($data['prof_deputy_costs_has_interim'] === 'no') {
                foreach ($report->getProfDeputyInterimCosts() as $ic) {
                    $this->getEntityManager()->remove($ic);
                }
            } else if ($data['prof_deputy_costs_has_interim'] === 'yes') {
                $report->setProfDeputyFixedCost(null);
            }
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        if (array_key_exists('prof_deputy_interim_costs', $data)) {
            // wipe existing interim costs in order to overwrite
            // TODO consider keeping and updating the existing ones if simpler to implement
            foreach ($report->getProfDeputyInterimCosts() as $ic) {
                $this->getEntityManager()->remove($ic);
            }
            // add new
            foreach ($data['prof_deputy_interim_costs'] as $row) {
                if ($row['date'] && $row['amount']) {
                    $report->addProfDeputyInterimCosts(
                        new EntityDir\Report\ProfDeputyInterimCost($report, new \DateTime($row['date']), $row['amount'])
                    );
                }
                if (count($report->getProfDeputyInterimCosts())) {
                    $report->setProfDeputyCostsHasInterim('yes');
                }
            }
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
            $this->getEntityManager()->flush();
        }

        if (array_key_exists('prof_deputy_fixed_cost', $data)) {
            $report->setProfDeputyFixedCost($data['prof_deputy_fixed_cost']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        if (array_key_exists('prof_deputy_costs_amount_to_scco', $data)) {
            $report->setProfDeputyCostsAmountToScco($data['prof_deputy_costs_amount_to_scco']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        if (array_key_exists('prof_deputy_costs_reason_beyond_estimate', $data)) {
            $report->setProfDeputyCostsReasonBeyondEstimate($data['prof_deputy_costs_reason_beyond_estimate']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        foreach ($this->updateHandlers as $updateHandler) {
            $updateHandler->handle($report, $data);
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
            'un_submit_date' => 'notEmpty',
            'due_date' => 'notEmpty',
            'unsubmitted_sections_list' => 'notEmpty',
            'start_date' => 'notEmpty',
            'end_date' => 'notEmpty',
        ]);

        $rs = $this->get('opg_digideps.report_service');
        /** @var $rs ReportService */
        $rs->unSubmit(
            $report,
            new \DateTime($data['un_submit_date']),
            new \DateTime($data['due_date']),
            new \DateTime($data['start_date']),
            new \DateTime($data['end_date']),
            $data['unsubmitted_sections_list']
        );

        $this->getEntityManager()->flush();

        return ['id' => $report->getId()];
    }

    /**
     * Update users's reports cached status when not set
     * Flushes every 5 records to allow resuming in case of timeouts
     *
     * @param $userId
     */
    private function updateReportStatusCache($userId)
    {
        $em = $this->get('em');
        $repo = $em->getRepository(Report::class);

        while (($reports = $repo
                ->createQueryBuilder('r')
                ->select('r,c,u')
                ->leftJoin('r.client', 'c')
                ->leftJoin('c.users', 'u')
                ->where('u.id = :uid')
                ->andWhere('r.reportStatusCached IS NULL')
                ->setParameter('uid', $userId)
                ->setMaxResults(5)
                ->getQuery()
                ->getResult()) && count($reports)
        ) {
            foreach ($reports as $report) {
                /* @var $report Report */
                $report->updateSectionsStatusCache($report->getAvailableSections());
            }
            $em->flush();
        }
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
        /* @var $rs ReportService */
        $rs = $this->get('opg_digideps.report_service');
        /**
         * @var $repo EntityDir\Repository\ReportRepository
         */
        $repo = $this->get('em')->getRepository(Report::class);

        $userId = $this->getUser()->getId(); //  take the PA user. Extend/remove when/if needed
        $offset = $request->get('offset');
        $q = $request->get('q');
        $status = $request->get('status');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort');
        $sortDirection = $request->get('sort_direction');
        $exclude_submitted = $request->get('exclude_submitted');

        // Calculate missing report statuses. Needed for the following code
        $this->updateReportStatusCache($userId);

        // calculate counts, and apply limit/offset
        $counts = [
            Report::STATUS_NOT_STARTED => $repo->getAllReportsQb('count', Report::STATUS_NOT_STARTED, $userId, $exclude_submitted, $q)->getQuery()->getSingleScalarResult(),
            Report::STATUS_NOT_FINISHED => $repo->getAllReportsQb('count', Report::STATUS_NOT_FINISHED, $userId, $exclude_submitted, $q)->getQuery()->getSingleScalarResult(),
            Report::STATUS_READY_TO_SUBMIT => $repo->getAllReportsQb('count', Report::STATUS_READY_TO_SUBMIT, $userId, $exclude_submitted, $q)->getQuery()->getSingleScalarResult()
        ];
        $counts['total'] = array_sum($counts);

        // Get reports for the current page, hydrating as array (more efficient) and return the min amount of data needed for the dashboard
        $qb = $repo->getAllReportsQb('reports', $status, $userId, $exclude_submitted, $q)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        if ($sort == 'end_date') {
            $qb->addOrderBy('r.endDate', strtolower($sortDirection) == 'desc' ? 'DESC' : 'ASC');
            $qb->addOrderBy('c.caseNumber', 'ASC');
        }

        /* @var $records Report[] */
        $reports = [];
        $reportArrays = $qb->getQuery()->getArrayResult();
        foreach ($reportArrays as $reportArray) {
            $reports[] = [
                'id' => $reportArray['id'],
                'type' => $reportArray['type'],
                'hasUnsumitDate' => $reportArray['unSubmitDate'] ? true : false,
                'status' => [
                    // adjust report status cached using end date
                    'status' => $rs->adjustReportStatus($reportArray['reportStatusCached'], $reportArray['endDate'])
                ],
                'due_date' => $reportArray['dueDate']->format('Y-m-d'),
                'client' => [
                    'id' => $reportArray['client']['id'],
                    'firstname' => $reportArray['client']['firstname'],
                    'lastname' => $reportArray['client']['lastname'],
                    'case_number' => $reportArray['client']['caseNumber'],
                ]
            ];
        }

        // if an unsubmitted report is present, delete the other non-unsubmitted client's reports
        foreach ($reports as $k => $unsubmittedReport) {
            if ($unsubmittedReport['hasUnsumitDate']) {
                foreach ($reports as $k2 => $currentReport) {
                    if (!$currentReport['hasUnsumitDate'] && $currentReport['client']['id'] == $unsubmittedReport['client']['id']) {
                        unset($reports[$k2]);
                    }
                }
            }
        }

        return [
            'counts' => $counts,
            'reports' => $reports,
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
        $checklist = $this->populateChecklistEntity($checklist, $checklistData);

        if (!empty($checklistData['further_information_received'])) {
            $info = new EntityDir\Report\ChecklistInformation($checklist, $checklistData['further_information_received']);
            $info->setCreatedBy($this->getUser());
            $this->getEntityManager()->persist($info);
        }

        if ($checklistData['button_clicked'] == 'submitAndDownload') {
            $checklist->setSubmittedBy(($this->getUser()));
            $checklist->setSubmittedOn(new \DateTime());
        }
        $checklist->setLastModifiedBy($this->getUser());

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

        $checklist = $this->populateChecklistEntity($checklist, $checklistData);

        if (!empty($checklistData['further_information_received'])) {
            $info = new EntityDir\Report\ChecklistInformation($checklist, $checklistData['further_information_received']);
            $info->setCreatedBy($this->getUser());
            $this->getEntityManager()->persist($info);
        }

        if ($checklistData['button_clicked'] == 'submitAndDownload') {
            $checklist->setSubmittedBy(($this->getUser()));
            $checklist->setSubmittedOn(new \DateTime());
        }

        $checklist->setLastModifiedBy($this->getUser());
        $this->persistAndFlush($checklist);

        return ['checklist' => $checklist->getId()];
    }

    private function populateChecklistEntity($checklist, $checklistData)
    {
        $this->hydrateEntityWithArrayData($checklist, $checklistData, [
            'reporting_period_accurate' => 'setReportingPeriodAccurate',
            'contact_details_upto_date' => 'setContactDetailsUptoDate',
            'deputy_full_name_accurate_in_casrec' => 'setDeputyFullNameAccurateInCasrec',
            'decisions_satisfactory' => 'setDecisionsSatisfactory',
            'consultations_satisfactory' => 'setConsultationsSatisfactory',
            'care_arrangements' => 'setCareArrangements',
            'assets_declared_and_managed' => 'setAssetsDeclaredAndManaged',
            'debts_managed' => 'setDebtsManaged',
            'open_closing_balances_match' => 'setOpenClosingBalancesMatch',
            'accounts_balance' => 'setAccountsBalance',
            'money_movements_acceptable' => 'setMoneyMovementsAcceptable',
            'satisfied_with_pa_expenses' => 'setSatisfiedWithPaExpenses',
            'bond_adequate' => 'setBondAdequate',
            'satisfied_with_health_and_lifestyle' => 'setSatisfiedWithHealthAndLifestyle',
            'bond_order_match_casrec' => 'setBondOrderMatchCasrec',
            'future_significant_decisions' => 'setFutureSignificantDecisions',
            'has_deputy_raised_concerns' => 'setHasDeputyRaisedConcerns',
            'case_worker_satisified' => 'setCaseWorkerSatisified',
            'lodging_summary' => 'setLodgingSummary',
            'final_decision' => 'setFinalDecision',
            'button_clicked' => 'setButtonClicked'
        ]);

        return $checklist;
    }
}
