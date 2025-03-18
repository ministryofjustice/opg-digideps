<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Exception\UnauthorisedException;
use App\Repository\ReportRepository;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use App\Service\ParameterStoreService;
use App\Service\ReportService;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/report")
 */
class ReportController extends RestController
{
    /** @var array */
    private $checklistGroups = [
        'report-id',
        'checklist',
        'user-name',
        'user-rolename',
        'user-email',
        'report-checklist',
        'report-sections',
        'prof-deputy-estimate-management-costs',
        'checklist-information',
        'report-client',
        'report-period',
        'client-name',
        'document-sync',
        'report-submission-uuid',
        'client-case-number',
        'report-submission-id',
    ];

    public function __construct(private readonly array $updateHandlers, private readonly ReportRepository $repository, private readonly ReportService $reportService, private readonly EntityManagerInterface $em, private readonly AuthService $authService, private readonly RestFormatter $formatter, private readonly ParameterStoreService $parameterStoreService)
    {
    }

    /**
     * Add a report
     * Currently only used by Lay deputy during registration steps
     * Pa report are instead created via OrgService::createReport().
     *
     * @Route("", methods={"POST"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function addAction(Request $request)
    {
        $reportData = $this->formatter->deserializeBodyContent($request);

        if (empty($reportData['client']['id'])) {
            throw new \InvalidArgumentException('Missing client.id');
        }
        $client = $this->findEntityBy(Client::class, $reportData['client']['id']);
        $this->denyAccessIfClientDoesNotBelongToUser($client);

        $this->formatter->validateArray($reportData, [
            'start_date' => 'notEmpty',
            'end_date' => 'notEmpty',
        ]);

        // report type is taken from Sirius. In case that's not available (shouldn't happen unless pre registration table is dropped), use a 102
        $reportType = $this->reportService->getReportTypeBasedOnSirius($client) ?: Report::LAY_PFA_HIGH_ASSETS_TYPE;
        $report = new Report($client, $reportType, new \DateTime($reportData['start_date']), new \DateTime($reportData['end_date']));
        $report->setReportSeen(true);

        $report->updateSectionsStatusCache($report->getAvailableSections());

        $this->em->persist($report);
        $this->em->flush();

        return ['report' => $report->getId()];
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')")
     *
     * @param int $id
     *
     * @return Report
     */
    public function getById(Request $request, $id)
    {
        $groups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['report'];

        $this->formatter->setJmsSerialiserGroups($groups);

        /* @var $report Report */
        if ($this->isGranted(User::ROLE_ADMIN)) {
            /** @var SoftDeleteableFilter $filter */
            $filter = $this->em->getFilters()->getFilter('softdeleteable');
            $filter->disableForEntity(Client::class);

            $report = $this->findEntityBy(Report::class, $id);
            $this->em->getFilters()->enable('softdeleteable');
        } else {
            $report = $this->findEntityBy(Report::class, $id);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
        }

        return $report;
    }

    /**
     * @Route("/{id}/submit", requirements={"id":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function submit(Request $request, $id)
    {
        $currentReport = $this->findEntityBy(Report::class, $id, 'Report not found');
        /* @var $currentReport Report */
        $this->denyAccessIfReportDoesNotBelongToUser($currentReport);

        $data = $this->formatter->deserializeBodyContent($request);

        if (empty($data['submit_date'])) {
            throw new \InvalidArgumentException('Missing submit_date');
        }

        if (empty($data['agreed_behalf_deputy'])) {
            throw new \InvalidArgumentException('Missing agreed_behalf_deputy');
        }

        /** @var User $user */
        $user = $this->getUser();
        if ('not_deputy' === $data['agreed_behalf_deputy'] && $user->isLayDeputy()) {
            throw new \InvalidArgumentException('\'not_deputy\' is invalid option of agreed_behalf_deputy for lay deputies');
        }

        $currentReport->setAgreedBehalfDeputy($data['agreed_behalf_deputy']);
        $xplanation = ('more_deputies_not_behalf' === $data['agreed_behalf_deputy'])
            ? $data['agreed_behalf_deputy_explanation'] : null;
        $currentReport->setAgreedBehalfDeputyExplanation($xplanation);

        /** @var User $user */
        $user = $this->getUser();

        /** @var Report|null $nextYearReport */
        $nextYearReport = $this->reportService->submit($currentReport, $user, new \DateTime($data['submit_date']));

        return $nextYearReport ? $nextYearReport->getId() : null;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')")
     */
    public function update(Request $request, $id)
    {
        /* @var $report Report */
        $report = $this->findEntityBy(Report::class, $id, 'Report not found');

        // deputies can only edit their own reports
        if (!$this->isGranted(User::ROLE_ADMIN)) {
            $this->denyAccessIfReportDoesNotBelongToUser($report);
        }

        $data = $this->formatter->deserializeBodyContent($request);

        if (isset($data['type'])) {
            $report->setType($data['type']);
            $report->updateSectionsStatusCache($report->getAvailableSections());
        }

        if (array_key_exists('has_debts', $data) && in_array($data['has_debts'], ['yes', 'no'])) {
            $report->setHasDebts($data['has_debts']);
            // null debts
            foreach ($report->getDebts() as $debt) {
                $debt->setAmount(null);
                $debt->setMoreDetails(null);
                $this->em->flush($debt);
            }
            // set debts as per "debts" key
            if ('yes' == $data['has_debts']) {
                foreach ($data['debts'] as $row) {
                    $debt = $report->getDebtByTypeId($row['debt_type_id']);
                    if (!$debt instanceof EntityDir\Report\Debt) {
                        continue; // not clear when that might happen. kept similar to transaction below
                    }
                    $debt->setAmountAndDetails($row['amount'], $row['more_details']);
                    $this->em->flush($debt);
                }
            }
            $this->formatter->setJmsSerialiserGroups(['debts']); // returns saved data (AJAX operations)
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DEBTS,
            ]);
        }

        if (array_key_exists('prof_deputy_other_costs', $data)) {
            $defaultCostTypeIds = array_column($report->getProfDeputyOtherCostTypeIds(), 'typeId');

            foreach ($data['prof_deputy_other_costs'] as $postedProfDeputyOtherCostType) {
                if (
                    in_array(
                        $postedProfDeputyOtherCostType['prof_deputy_other_cost_type_id'],
                        $defaultCostTypeIds
                    )
                ) {
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

                    $this->em->persist($profDeputyOtherCost);
                }
            }
            $report->updateSectionsStatusCache([
                Report::SECTION_PROF_DEPUTY_COSTS,
            ]);
            $this->em->flush();
        }

        if (array_key_exists('debt_management', $data)) {
            $report->setDebtManagement($data['debt_management']);
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DEBTS,
            ]);
        }

        if (array_key_exists('fees', $data)) {
            foreach ($data['fees'] as $row) {
                $fee = $report->getFeeByTypeId($row['fee_type_id']);
                if (!$fee instanceof EntityDir\Report\Fee) {
                    continue; // not clear when that might happen. kept similar to transaction below
                }
                $fee->setAmountAndDetails($row['amount'], $row['more_details']);
                $this->em->flush($fee);
            }
            $report->updateSectionsStatusCache([
                Report::SECTION_DEPUTY_EXPENSES,
                Report::SECTION_PA_DEPUTY_EXPENSES,
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
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DEPUTY_EXPENSES,
                Report::SECTION_PA_DEPUTY_EXPENSES,
            ]);
        }

        if (array_key_exists('paid_for_anything', $data)) {
            if ('no' === $data['paid_for_anything']) { // remove existing expenses
                foreach ($report->getExpenses() as $e) {
                    $this->em->remove($e);
                }
            }
            $report->setPaidForAnything($data['paid_for_anything']);
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DEPUTY_EXPENSES,
                Report::SECTION_PA_DEPUTY_EXPENSES,
            ]);
        }

        if (array_key_exists('gifts_exist', $data)) {
            if ('no' === $data['gifts_exist']) { // remove existing gift
                foreach ($report->getGifts() as $e) {
                    $this->em->remove($e);
                }
            }
            $report->setGiftsExist($data['gifts_exist']);
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_GIFTS,
            ]);
        }

        if (array_key_exists('due_date', $data)) {
            $report->setDueDate(new \DateTime($data['due_date']));
        }

        if (array_key_exists('start_date', $data)) {
            $report->setStartDate(new \DateTime($data['start_date']));
        }

        if (array_key_exists('end_date', $data)) {
            $report->setEndDate(new \DateTime($data['end_date']));
            // end date could be updated automatically with a listener, but better not to overload
            // the default behaviour until the logic is 100% clear
            $report->updateDueDateBasedOnEndDate();
        }

        if (array_key_exists('report_seen', $data)) {
            $report->setReportSeen((bool) $data['report_seen']);
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
                    $this->em->remove($asset);
                }
                $this->em->flush();
            }
            $report->updateSectionsStatusCache([
                Report::SECTION_ASSETS,
            ]);
        }

        if (array_key_exists('no_transfers_to_add', $data)) {
            if (true === $data['no_transfers_to_add']) {
                // true here means "no", so remove existing transfers
                foreach ($report->getMoneyTransfers() as $e) {
                    $this->em->remove($e);
                }
            }
            $report->setNoTransfersToAdd($data['no_transfers_to_add']);
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_TRANSFERS,
            ]);
        }

        if (array_key_exists('significant_decisions_made', $data)) {
            $report->setSignificantDecisionsMade($data['significant_decisions_made']);
            $report->updateSectionsStatusCache([
                Report::SECTION_DECISIONS,
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
                    'yes' == $data['action_more_info'] ? $data['action_more_info_details'] : null
                );
            }
            $report->updateSectionsStatusCache([
                Report::SECTION_OTHER_INFO,
            ]);
        }

        if (array_key_exists('money_in_exists', $data)) {
            $report->setMoneyInExists($data['money_in_exists']);
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_IN,
            ]);
        }

        if (array_key_exists('reason_for_no_money_in', $data)) {
            $report->setReasonForNoMoneyIn($data['reason_for_no_money_in']);
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_IN,
            ]);
        }

        if (array_key_exists('money_out_exists', $data)) {
            $report->setMoneyOutExists($data['money_out_exists']);
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_OUT,
            ]);
        }

        if (array_key_exists('reason_for_no_money_out', $data)) {
            $report->setReasonForNoMoneyOut($data['reason_for_no_money_out']);
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_OUT,
            ]);
        }

        if (array_key_exists('money_short_categories_in', $data)) {
            foreach ($data['money_short_categories_in'] as $row) {
                $e = $report->getMoneyShortCategoryByTypeId($row['type_id']);
                if ($e instanceof EntityDir\Report\MoneyShortCategory) {
                    $e
                        ->setPresent($row['present']);
                    $this->em->flush($e);
                }
            }
            $this->em->flush();
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
                    $this->em->flush($e);
                }
            }
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_IN_SHORT,
                Report::SECTION_MONEY_OUT_SHORT,
            ]);
        }

        if (array_key_exists('money_transactions_short_in_exist', $data)) {
            if ('no' === $data['money_transactions_short_in_exist']) { // remove existing
                foreach ($report->getMoneyTransactionsShortIn() as $e) {
                    $this->em->remove($e);
                }
            }
            $report->setMoneyTransactionsShortInExist($data['money_transactions_short_in_exist']);
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_MONEY_IN_SHORT,
                Report::SECTION_MONEY_OUT_SHORT,
            ]);
        }

        if (array_key_exists('money_transactions_short_out_exist', $data)) {
            if ('no' === $data['money_transactions_short_out_exist']) { // remove existing
                foreach ($report->getMoneyTransactionsShortOut() as $e) {
                    $this->em->remove($e);
                }
            }
            $report->setMoneyTransactionsShortOutExist($data['money_transactions_short_out_exist']);
            $this->em->flush();
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
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_DOCUMENTS,
            ]);
        }

        if (array_key_exists('previous_prof_fees_estimate_given', $data)) {
            $report->setPreviousProfFeesEstimateGiven($data['previous_prof_fees_estimate_given']);
            if ('no' === $data['previous_prof_fees_estimate_given']) {
                $report->setProfFeesEstimateSccoReason(null);
            } else {
                $report->setProfFeesEstimateSccoReason($data['prof_fees_estimate_scco_reason']);
            }
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_PROF_CURRENT_FEES,
            ]);
        }

        if (array_key_exists('current_prof_payments_received', $data)) {
            if ('no' == $data['current_prof_payments_received']) { // reset whole section
                foreach ($report->getCurrentProfServiceFees() as $f) {
                    $this->em->remove($f);
                }
                $report->setPreviousProfFeesEstimateGiven(null);
                $report->setProfFeesEstimateSccoReason(null);
            }
            $report->setCurrentProfPaymentsReceived($data['current_prof_payments_received']);
            $this->em->flush();
            $report->updateSectionsStatusCache([
                Report::SECTION_PROF_CURRENT_FEES,
            ]);
        }

        if (array_key_exists('submitted', $data)) {
            $report->setSubmitted($data['submitted']);
        }

        if (array_key_exists('unsubmit_date', $data)) {
            $report->setUnSubmitDate($data);
        }

        foreach ($this->updateHandlers as $updateHandler) {
            $updateHandler->handle($report, $data);
        }

        $this->em->flush();

        return ['id' => $report->getId()];
    }

    /**
     * @Route("/{id}/unsubmit", requirements={"id":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function unsubmit(Request $request, $id)
    {
        /** @var Report $report */
        $report = $this->findEntityBy(Report::class, $id, 'Report not found');
        if (!$report->getSubmitted()) {
            throw new \RuntimeException('Cannot unsubmit an active report');
        }

        $data = $this->formatter->deserializeBodyContent($request, [
            'un_submit_date' => 'notEmpty',
            'due_date' => 'notEmpty',
            'unsubmitted_sections_list' => 'notEmpty',
            'start_date' => 'notEmpty',
            'end_date' => 'notEmpty',
        ]);

        $this->reportService->unSubmit(
            $report,
            new \DateTime($data['un_submit_date']),
            new \DateTime($data['due_date']),
            new \DateTime($data['start_date']),
            new \DateTime($data['end_date']),
            $data['unsubmitted_sections_list']
        );

        $this->em->flush();

        return ['id' => $report->getId()];
    }

    /**
     * @Route("/get-all-by-user", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ORG')")
     *
     * @throws NonUniqueResultException
     */
    public function getAllByUser(Request $request): array
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->getResponseByDeterminant($request, $user->getId(), ReportRepository::USER_DETERMINANT);
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getResponseByDeterminant(Request $request, $orgIdsOrUserId, int $determinant): array
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = $this->repository->getAllByDeterminant($orgIdsOrUserId, $determinant, $request->query, 'reports', $request->query->get('status'));
        $this->updateReportStatusCache($user->getId());

        $result = [];
        $result['reports'] = (null === $data) ? [] : $this->transformReports($data);
        $result['counts'] = $this->getReportCountsByStatus($request, $orgIdsOrUserId, $determinant);

        return $result;
    }

    /**
     * Update users's reports cached status when not set
     * Flushes every 5 records to allow resuming in case of timeouts.
     *
     * @param int $userId
     */
    private function updateReportStatusCache($userId)
    {
        /** @var ReportRepository $repo */
        $repo = $this->em->getRepository(Report::class);

        while (
            ($reports = $repo
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

            $this->em->flush();
        }
    }

    private function transformReports(array $reportData): array
    {
        $reports = [];
        foreach ($reportData as $reportDatum) {
            $reports[] = [
                'id' => $reportDatum['id'],
                'type' => $reportDatum['type'],
                'un_submit_date' => $reportDatum['unSubmitDate'] instanceof \DateTime ?
                    $reportDatum['unSubmitDate']->format('Y-m-d') : null,
                'status' => [
                    // adjust report status cached using end date
                    'status' => $this->reportService->adjustReportStatus($reportDatum['reportStatusCached'], $reportDatum['endDate']),
                ],
                'due_date' => $reportDatum['dueDate']->format('Y-m-d'),
                'client' => [
                    'id' => $reportDatum['client']['id'],
                    'firstname' => $reportDatum['client']['firstname'],
                    'lastname' => $reportDatum['client']['lastname'],
                    'case_number' => $reportDatum['client']['caseNumber'],
                    'organisation' => [
                        'name' => $reportDatum['client']['organisation']['name'],
                    ],
                ],
            ];
        }

        return $reports;
    }

    /**
     * @throws \Exception
     */
    private function getReportCountsByStatus(Request $request, $orgIdsOrUserId, int $determinant): array
    {
        $counts = [
            Report::STATUS_NOT_STARTED => $this->getCountOfReportsByStatus(Report::STATUS_NOT_STARTED, $orgIdsOrUserId, $determinant, $request),
            Report::STATUS_NOT_FINISHED => $this->getCountOfReportsByStatus(Report::STATUS_NOT_FINISHED, $orgIdsOrUserId, $determinant, $request),
            Report::STATUS_READY_TO_SUBMIT => $this->getCountOfReportsByStatus(Report::STATUS_READY_TO_SUBMIT, $orgIdsOrUserId, $determinant, $request),
        ];

        $counts['total'] = array_sum($counts);

        return $counts;
    }

    /**
     * @param int $id
     *
     * @return array|mixed|null
     *
     * @throws NonUniqueResultException
     */
    private function getCountOfReportsByStatus(string $status, $id, int $determinant, Request $request)
    {
        return $this
            ->repository
            ->getAllByDeterminant($id, $determinant, $request->query, 'count', $status);
    }

    /**
     * @Route("/get-all-by-orgs", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ORG')")
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getAllByOrgs(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var array $organisationIds */
        $organisationIds = $user->getOrganisationIds();

        if (empty($organisationIds)) {
            throw new NotFoundHttpException('No organisations found for user');
        }

        return $this->getResponseByDeterminant($request, $organisationIds, ReportRepository::ORG_DETERMINANT);
    }

    /**
     * @Route("/{id}/submit-documents", requirements={"id":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function submitDocuments($id)
    {
        /* @var Report $currentReport */
        $currentReport = $this->findEntityBy(Report::class, $id, 'Report not found');
        $this->denyAccessIfReportDoesNotBelongToUser($currentReport);

        /** @var User $user */
        $user = $this->getUser();

        $this->reportService->submitAdditionalDocuments($currentReport, $user, new \DateTime());

        return ['reportId' => $currentReport->getId()];
    }

    /**
     * Add a checklist for the report.
     *
     * @Route("/{report_id}/checked", requirements={"report_id":"\d+"}, methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function insertChecklist(Request $request, $report_id)
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Report $report */
        $report = $this->findEntityBy(Report::class, $report_id, 'Report not found');

        $checklistData = $this->formatter->deserializeBodyContent($request);

        /** @var EntityDir\Report\Checklist $checklist */
        $checklist = new EntityDir\Report\Checklist($report);
        $checklist = $this->populateChecklistEntity($checklist, $checklistData);

        if (!empty($checklistData['further_information_received'])) {
            $info = new EntityDir\Report\ChecklistInformation($checklist, $checklistData['further_information_received']);
            $info->setCreatedBy($user);
            $this->em->persist($info);
        }

        if ('submitAndContinue' == $checklistData['button_clicked']) {
            $checklist->setSubmittedBy($user);
            $checklist->setSubmittedOn(new \DateTime());
        }
        $checklist->setLastModifiedBy($user);

        $this->em->persist($checklist);
        $this->em->flush();

        return ['checklist' => $checklist->getId()];
    }

    private function populateChecklistEntity($checklist, $checklistData)
    {
        $this->hydrateEntityWithArrayData($checklist, $checklistData, [
            'accounts_balance' => 'setAccountsBalance',
            'assets_declared_and_managed' => 'setAssetsDeclaredAndManaged',
            'bond_adequate' => 'setBondAdequate',
            'bond_order_match_sirius' => 'setBondOrderMatchSirius',
            'button_clicked' => 'setButtonClicked',
            'care_arrangements' => 'setCareArrangements',
            'case_worker_satisified' => 'setCaseWorkerSatisified',
            'client_benefits_checked' => 'setClientBenefitsChecked',
            'consultations_satisfactory' => 'setConsultationsSatisfactory',
            'contact_details_upto_date' => 'setContactDetailsUptoDate',
            'decisions_satisfactory' => 'setDecisionsSatisfactory',
            'debts_managed' => 'setDebtsManaged',
            'deputy_charge_allowed_by_court' => 'setDeputyChargeAllowedByCourt',
            'deputy_full_name_accurate_in_sirius' => 'setDeputyFullNameAccurateInSirius',
            'final_decision' => 'setFinalDecision',
            'future_significant_decisions' => 'setFutureSignificantDecisions',
            'has_deputy_overcharged_from_previous_estimates' => 'setHasDeputyOverchargedFromPreviousEstimates',
            'has_deputy_raised_concerns' => 'setHasDeputyRaisedConcerns',
            'lodging_summary' => 'setLodgingSummary',
            'money_movements_acceptable' => 'setMoneyMovementsAcceptable',
            'next_billing_estimates_satisfactory' => 'setNextBillingEstimatesSatisfactory',
            'open_closing_balances_match' => 'setOpenClosingBalancesMatch',
            'payments_match_cost_certificate' => 'setPaymentsMatchCostCertificate',
            'prof_costs_reasonable_and_proportionate' => 'setProfCostsReasonableAndProportionate',
            'reporting_period_accurate' => 'setReportingPeriodAccurate',
            'satisfied_with_health_and_lifestyle' => 'setSatisfiedWithHealthAndLifestyle',
            'satisfied_with_pa_expenses' => 'setSatisfiedWithPaExpenses',
            'synchronisation_status' => 'setSynchronisationStatus',
        ]);

        return $checklist;
    }

    /**
     * Update a checklist for the report.
     *
     * @Route("/{report_id}/checked", requirements={"report_id":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function updateChecklist(Request $request, $report_id)
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Report $report */
        $report = $this->findEntityBy(Report::class, $report_id, 'Report not found');

        $checklistData = $this->formatter->deserializeBodyContent($request);

        /** @var EntityDir\Report\Checklist $checklist */
        $checklist = $report->getChecklist();

        $checklist = $this->populateChecklistEntity($checklist, $checklistData);

        if (!empty($checklistData['further_information_received'])) {
            $info = new EntityDir\Report\ChecklistInformation($checklist, $checklistData['further_information_received']);
            $info->setCreatedBy($user);
            $this->em->persist($info);
        }

        if (isset($checklistData['button_clicked']) && 'submitAndContinue' == $checklistData['button_clicked']) {
            $checklist->setSubmittedBy($user);
            $checklist->setSubmittedOn(new \DateTime());
        }

        $checklist->setLastModifiedBy($user);

        $this->em->persist($checklist);
        $this->em->flush();

        return ['checklist' => $checklist->getId()];
    }

    /**
     * Get a checklist for the report.
     *
     * @Route("/{report_id}/checklist", requirements={"report_id":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getChecklist(Request $request, $report_id)
    {
        $this->formatter->setJmsSerialiserGroups(['checklist', 'last-modified', 'user']);

        $checklist = $this
            ->getRepository(EntityDir\Report\ReviewChecklist::class)
            ->findOneBy(['report' => $report_id]);

        return $checklist;
    }

    /**
     * Update a checklist for the report.
     *
     * @Route("/{report_id}/checklist", requirements={"report_id":"\d+"}, methods={"POST", "PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function upsertChecklist(Request $request, $report_id)
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Report $report */
        $report = $this->findEntityBy(Report::class, $report_id, 'Report not found');

        $checklistData = $this->formatter->deserializeBodyContent($request);

        /** @var EntityDir\Report\ReviewChecklist|null $checklist */
        $checklist = $this
            ->getRepository(EntityDir\Report\ReviewChecklist::class)
            ->findOneBy(['report' => $report->getId()]);

        if (is_null($checklist)) {
            $checklist = new EntityDir\Report\ReviewChecklist($report);
        }

        $checklist
            ->setAnswers($checklistData['answers'])
            ->setDecision($checklistData['decision']);

        if ($checklistData['is_submitted']) {
            $checklist->setSubmittedBy($user);
            $checklist->setSubmittedOn(new \DateTime());
        }

        $checklist->setLastModifiedBy($user);

        $this->em->persist($checklist);
        $this->em->flush();

        return ['checklist' => $checklist->getId()];
    }

    /**
     * @Route("/all-with-queued-checklists", methods={"GET"})
     *
     * @throws DBALException
     */
    public function getReportsWithQueuedChecklists(Request $request): array
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        /** @var array $data */
        $data = $this->formatter->deserializeBodyContent($request);

        /** @var ReportRepository $reportRepo */
        $reportRepo = $this->em->getRepository(Report::class);
        $queuedReportIds = $reportRepo->getReportsIdsWithQueuedChecklistsAndSetChecklistsToInProgress(intval($data['row_limit']));

        $reports = [];
        foreach ($queuedReportIds as $reportId) {
            $filter = $this->em->getFilters()->getFilter('softdeleteable');
            $filter->disableForEntity(Client::class);

            $reports[] = $this->findEntityBy(Report::class, $reportId);
        }

        $this->formatter->setJmsSerialiserGroups($this->checklistGroups);

        return $reports;
    }

    /**
     * @Route("/{reportId}/refresh-cache", methods={"POST"}, name="refresh_report_cache")
     */
    public function refreshReportCache(Request $request, int $reportId)
    {
        $groups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['report', 'client', 'client-report'];

        $this->formatter->setJmsSerialiserGroups($groups);

        /** @var array $data */
        $data = $this->formatter->deserializeBodyContent($request);

        if (!isset($data['sectionIds']) || empty($data['sectionIds'])) {
            throw new \InvalidArgumentException('SectionIds are required to refresh the Report cache');
        }

        /** @var ReportRepository $reportRepo */
        $reportRepo = $this->em->getRepository(Report::class);
        $report = $reportRepo->find($reportId);

        $report->updateSectionsStatusCache($data['sectionIds']);

        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }
}
