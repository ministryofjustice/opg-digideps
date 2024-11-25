<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\Report\MoneyTransactionShort;
use App\Entity\Report\Status;
use App\Entity\User;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MoneyInShortController extends AbstractController
{
    private static $jmsGroups = [
        'moneyShortCategoriesIn',
        'moneyTransactionsShortIn',
        'money-in-short-state',
    ];

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
        private ClientApi $clientApi
    ) {
    }

    /**
     * @Route("/report/{reportId}/money-in-short", name="money_in_short")
     *
     * @Template("@App/Report/MoneyInShort/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getMoneyInShortState()['state']) {
            return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/exist", name="does_money_in_short_exist")
     *
     * @Template("@App/Report/MoneyInShort/exist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\Report\DoesMoneyInExistType::class,
            $report,
            ['translation_domain' => 'report-money-short']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['moneyInExists']->getData();

            $report->setMoneyInExists($answer);
            $this->restClient->put('report/'.$reportId, $report, ['doesMoneyInExist']);

            if ('Yes' === $answer) {
                $report->setReasonForNoMoneyIn(null);
                $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyIn']);

                return $this->redirectToRoute('money_in_short_category', ['reportId' => $reportId, 'from' => 'does_money_in_short_exist']);
            } else {
                $this->cleanDataIfAnswerIsChangedFromYesToNo($report);
                $this->restClient->put('report/'.$reportId, $report, ['moneyShortCategoriesIn']);

                return $this->redirectToRoute('no_money_in_short_exists', ['reportId' => $reportId, 'from' => 'does_money_in_short_exist']);
            }
        }

        $backLink = $this->generateUrl('money_in_short', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    private function cleanDataIfAnswerIsChangedFromYesToNo($report): void
    {
        // selected categories in money short category table are set to false
        foreach ($report->getMoneyShortCategoriesIn() as $shortCategories) {
            if ($shortCategories->isPresent()) {
                $shortCategories->setPresent(false);
            }
        }

        // all transactions are deleted from 'money transaction short' table if present
        $reportId = $report->getId();

        if ($report->getMoneyTransactionsShortInExist()) {
            $report->setMoneyTransactionsShortInExist('no');

            $this->restClient->put('report/'.$reportId, $report, ['money-transactions-short-in-exist']);
        }
    }

    /**
     * @Route("/report/{reportId}/money-in-short/no-money-in-short-exists", name="no_money_in_short_exists")
     *
     * @Template("@App/Report/MoneyInShort/noMoneyInShortToReport.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function noMoneyInShortToReport(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\NoMoneyInType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['reasonForNoMoneyIn']->getData();

            $report->setReasonForNoMoneyIn($answer);
            $report->getStatus()->setMoneyInShortState(Status::STATE_DONE);
            $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyIn']);

            return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('does_money_in_short_exist', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/category", name="money_in_short_category")
     *
     * @Template("@App/Report/MoneyInShort/category.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function categoryAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = 'summary' == $request->get('from');

        $form = $this->createForm(FormDir\Report\MoneyShortType::class, $report, ['field' => 'moneyShortCategoriesIn']);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->restClient->put('report/'.$reportId, $data, ['moneyShortCategoriesIn']);

            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );

                return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('money_in_short_one_off_payments_exist', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_in_short_summary' : 'does_money_in_short_exist', ['reportId' => $reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/oneOffPaymentsExist", name="money_in_short_one_off_payments_exist")
     *
     * @Template("@App/Report/MoneyInShort/oneOffPaymentsExist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function oneOffPaymentsExistAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            ['field' => 'moneyTransactionsShortInExist', 'translation_domain' => 'report-money-short']
        );
        $form->handleRequest($request);
        $fromSummaryPage = 'summary' == $request->get('from');

        // retrieve soft deleted transaction ids if present and store money in short ids only
        $softDeletedTransactionIds = $this->restClient->get('/report/'.$reportId.'/money-transaction-short/get-soft-delete', 'array');

        $softDeletedMoneyInShortTransactionIds = [];
        foreach ($softDeletedTransactionIds as $softDeletedTransactionId) {
            if ('in' == $softDeletedTransactionId['type']) {
                $softDeletedMoneyInShortTransactionIds[] = $softDeletedTransactionId['id'];
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */

            $this->restClient->put('report/'.$reportId, $data, ['money-transactions-short-in-exist']);

            if ('yes' === $data->getMoneyTransactionsShortInExist() && !empty($softDeletedMoneyInShortTransactionIds)) {
                // undelete items if they exist
                foreach ($softDeletedMoneyInShortTransactionIds as $transactionId) {
                    $this->restClient->put('/report/'.$reportId.'/money-transaction-short/soft-delete/'.$transactionId, ['transactionSoftDelete']);
                }

                return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId, 'from' => 'money_in_short_one_off_payments_exist']);
            } elseif ('yes' === $data->getMoneyTransactionsShortInExist() && !empty($data->getMoneyTransactionsShortIn()) && 'summary' == $fromSummaryPage) {
                // covers scenarios where deputy clicks edit link from summary change but chooses not to change answer
                return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId, 'from' => 'money_in_short_one_off_payments_exist']);
            }

            switch ($data->getMoneyTransactionsShortInExist()) {
                case 'yes':
                    return $this->redirectToRoute('money_in_short_add', ['reportId' => $reportId]);
                case 'no':
                    return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_in_short_summary' : 'money_in_short_category', ['reportId' => $reportId]), // FIX when from summary
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/add", name="money_in_short_add")
     *
     * @Template("@App/Report/MoneyInShort/add.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $record = new MoneyTransactionShort('in');
        $record->setReport($report);
        $fromSummaryPage = 'summary' == $request->get('from');

        $form = $this->createForm(FormDir\Report\MoneyShortTransactionType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->post('report/'.$report->getId().'/money-transaction-short', $data, ['moneyTransactionShort']);

            return $this->redirect($this->generateUrl('money_in_short_add_another', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_in_short_summary' : 'money_in_short_one_off_payments_exist', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/add_another", name="money_in_short_add_another")
     *
     * @Template("@App/Report/MoneyInShort/addAnother.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-money-short']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('money_in_short_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/edit/{transactionId}", name="money_in_short_edit")
     *
     * @Template("@App/Report/MoneyInShort/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $transaction = $this->restClient->get('report/'.$report->getId().'/money-transaction-short/'.$transactionId, 'Report\MoneyTransactionShort');

        $form = $this->createForm(FormDir\Report\MoneyShortTransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Entry edited');

            $this->restClient->put('report/'.$report->getId().'/money-transaction-short/'.$transaction->getId(), $data, ['moneyTransactionShort']);

            return $this->redirect($this->generateUrl('money_in_short_summary', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('money_in_short_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/{transactionId}/delete", name="money_in_short_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $transactionId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('report/'.$report->getId().'/money-transaction-short/'.$transactionId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Entry deleted'
            );

            return $this->redirect($this->generateUrl('money_in_short_summary', ['reportId' => $reportId]));
        }

        $transaction = $this->restClient->get('report/'.$report->getId().'/money-transaction-short/'.$transactionId, 'Report\MoneyTransactionShort');

        return [
            'translationDomain' => 'report-money-in',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.description', 'value' => $transaction->getDescription()],
                ['label' => 'deletePage.summary.date', 'value' => $transaction->getDate(), 'format' => 'date'],
                ['label' => 'deletePage.summary.amount', 'value' => $transaction->getAmount(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('money_in_short_summary', ['reportId' => $reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/summary", name="money_in_short_summary")
     *
     * @Template("@App/Report/MoneyInShort/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyInShortState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('money_in_short', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'moneyInShort';
    }
}
