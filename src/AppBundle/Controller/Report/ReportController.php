<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     * Pa report are instead created via PaService::createReport()
     *
     * @Route("")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

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

        $this->persistAndFlush($report);

        return ['report' => $report->getId()];
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function getById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $groups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['report'];
        $this->setJmsSerialiserGroups($groups);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $id);
        /* @var $report Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        return $report;
    }

    /**
     * @Route("/{id}/submit", requirements={"id":"\d+"})
     * @Method({"PUT"})
     */
    public function submit(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $currentReport = $this->findEntityBy(EntityDir\Report\Report::class, $id, 'Report not found');
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
        $currentReport->setSubmittedBy($this->getUser());
        $currentReport->setSubmitDate(new \DateTime($data['submit_date']));

        //lets create subsequent year's report
        $nextYearReport = $this->get('opg_digideps.report_service')->createNextYearReport($currentReport);
        $this->getEntityManager()->flush($currentReport);

        //response to pass back
        return ['newReportId' => $nextYearReport->getId()];
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $id, 'Report not found');
        /* @var $report Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request);


        //TODO move to a unit-tested service
        if (!empty($data['type'])) {
            $report->setType($data['type']);
            // enable if SQL report type is not needed anymore
            //$this->getRepository(Report::class)->addMoneyShortCategoriesIfMissing($report);
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

        $this->getEntityManager()->flush();

        return ['id' => $report->getId()];
    }

    /**
     * Get list of reports, currently only for PA users
     *
     *
     * @Route("/get-all")
     * @Method({"GET"})
     */
    public function getAll(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_PA, EntityDir\User::ROLE_PA_ADMIN, EntityDir\User::ROLE_PA_TEAM_MEMBER]);

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
            $qb->orderBy('r.endDate', strtolower($sortDirection) == 'desc' ? 'DESC' : 'ASC');
        }

        if ($q) {
            $qb->andWhere('lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike OR c.caseNumber = :q');
            $qb->setParameter('qLike', '%' . strtolower($q) . '%');
            $qb->setParameter('q', $q);
        }

        $reports = $qb->getQuery()->getResult(); /* @var $reports Report[] */

        // calculate counts, and apply limit/offset
        $counts = ['total' => 0,
                   'notStarted' => 0,
                   'notFinished' => 0,
                   'readyToSubmit' => 0];
        foreach ($reports as $report) {
            $counts[$report->getStatus()->getStatus()]++;
            $counts['total']++;
        }

        // status filters
        if ($status) {
            $reports = array_filter($reports, function ($report) use ($status) {
                return $report->getStatus()->getStatus() == $status;
            });
        }
        // apply offset and limit filters (has to be last)
        $reports = array_slice($reports, $offset, $limit);


        $serialisedGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['client', 'report'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return [
            'counts'=>$counts,
            'reports'=>$reports
        ];
    }

    /**
     * Get list of reports, currently only for PA users
     *
     *
     * @Route("/get-submitted")
     * @Method({"GET"})
     */
    public function getSubmitted(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_ADMIN]);

        $qb = $this->getRepository(EntityDir\Report\Report::class)->createQueryBuilder('r');
        $qb
            ->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            ->where('r.submitted = true')
            ->orderBy('r.submittedBy', 'DESC')
        ;

        $serialisedGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['report'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $qb->getQuery()->getResult();
    }
}
