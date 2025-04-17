<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\Report\MoneyTransactionShort;
use App\Entity\Report\Status;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MoneyOutShortController extends AbstractController
{
    private static $jmsGroups = [
        'moneyShortCategoriesOut',
        'moneyTransactionsShortOut',
        'money-out-short-state',
    ];

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
    ) {
    }

    /**
     * @Route("/report/{reportId}/money-out-short", name="money_out_short")
     *
     * @Template("@App/Report/MoneyOutShort/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getMoneyOutShortState()['state']) {
            return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/exist", name="does_money_out_short_exist")
     *
     * @Template("@App/Report/MoneyOutShort/exist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\Report\DoesMoneyOutExistType::class,
            $report,
            ['translation_domain' => 'report-money-short']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['moneyOutExists']->getData();

            $report->setMoneyOutExists($answer);
            $this->restClient->put('report/'.$reportId, $report, ['doesMoneyOutExist']);

            if ('Yes' === $answer) {
                $report->setReasonForNoMoneyOut(null);
                $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyOut']);

                return $this->redirectToRoute('money_out_short_category', ['reportId' => $reportId, 'from' => 'does_money_out_short_exist']);
            } else {
                $this->cleanDataIfAnswerIsChangedFromYesToNo($report);
                $this->restClient->put('report/'.$reportId, $report, ['moneyShortCategoriesOut']);

                return $this->redirectToRoute('no_money_out_short_exists', ['reportId' => $reportId, 'from' => 'does_money_out_short_exist']);
            }
        }

        $backLink = $this->generateUrl('money_out_short', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    private function cleanDataIfAnswerIsChangedFromYesToNo($report): void
    {
        // selected categories in money short category table are set to false
        foreach ($report->getMoneyShortCategoriesOut() as $shortCategories) {
            if ($shortCategories->isPresent()) {
                $shortCategories->setPresent(false);
            }
        }

        // and all transactions are deleted from 'money transaction short' table if present
        $reportId = $report->getId();

        if ($report->getMoneyTransactionsShortOutExist()) {
            $report->setMoneyTransactionsShortOutExist('no');

            $this->restClient->put('report/'.$reportId, $report, ['money-transactions-short-out-exist']);
        }
    }

    /**
     * @Route("/report/{reportId}/money-out-short/no-money-out-short-exists", name="no_money_out_short_exists")
     *
     * @Template("@App/Report/MoneyOutShort/noMoneyOutShortToReport.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function noMoneyOutShortToReport(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\NoMoneyOutType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['reasonForNoMoneyOut']->getData();

            $report->setReasonForNoMoneyOut($answer);
            $report->getStatus()->setMoneyOutState(Status::STATE_DONE);

            $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyOut']);

            return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('does_money_out_short_exist', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/category", name="money_out_short_category")
     *
     * @Template("@App/Report/MoneyOutShort/category.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function categoryAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = 'summary' == $request->get('from');

        $form = $this->createForm(FormDir\Report\MoneyShortType::class, $report, ['field' => 'moneyShortCategoriesOut']);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->restClient->put('report/'.$reportId, $data, ['moneyShortCategoriesOut']);

            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );

                return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('money_out_short_one_off_payments_exist', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_out_short_summary' : 'does_money_out_short_exist', ['reportId' => $reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/oneOffPaymentsExist", name="money_out_short_one_off_payments_exist")
     *
     * @Template("@App/Report/MoneyOutShort/oneOffPaymentsExist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function oneOffPaymentsExistAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            ['field' => 'moneyTransactionsShortOutExist', 'translation_domain' => 'report-money-short']
        );
        $form->handleRequest($request);
        $fromSummaryPage = 'summary' == $request->get('from');

        // retrieve soft deleted transaction ids if present and store money out short ids only
        $softDeletedTransactionIds = $this->restClient->get('/report/'.$reportId.'/money-transaction-short/get-soft-delete', 'array');

        $softDeletedMoneyOutShortTransactionIds = [];
        foreach ($softDeletedTransactionIds as $softDeletedTransactionId) {
            if ('out' == $softDeletedTransactionId['type']) {
                $softDeletedMoneyOutShortTransactionIds[] = $softDeletedTransactionId['id'];
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */

            $this->restClient->put('report/'.$reportId, $data, ['money-transactions-short-out-exist']);

            // undelete items if they exist
            if ('yes' === $data->getMoneyTransactionsShortOutExist() && !empty($softDeletedMoneyOutShortTransactionIds)) {
                foreach ($softDeletedMoneyOutShortTransactionIds as $transactionId) {
                    $this->restClient->put('/report/'.$reportId.'/money-transaction-short/soft-delete/'.$transactionId, ['transactionSoftDelete']);
                }

                return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId, 'from' => 'money_out_short_one_off_payments_exist']);
            } elseif ('yes' === $data->getMoneyTransactionsShortOutExist() && !empty($data->getMoneyTransactionsShortOut()) && 'summary' == $fromSummaryPage) {
                return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId, 'from' => 'money_out_short_one_off_payments_exist']);
            }

            switch ($data->getMoneyTransactionsShortOutExist()) {
                case 'yes':
                    return $this->redirectToRoute('money_out_short_add', ['reportId' => $reportId, 'from' => 'exist']);
                case 'no':
                    return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_out_short_summary' : 'money_out_short_category', ['reportId' => $reportId]), // FIX when from summary
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/add", name="money_out_short_add")
     *
     * @Template("@App/Report/MoneyOutShort/add.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $record = new MoneyTransactionShort('out');
        $record->setReport($report);

        $fromSummaryPage = 'summary' == $request->get('from');

        $form = $this->createForm(FormDir\Report\MoneyShortTransactionType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->post('report/'.$report->getId().'/money-transaction-short', $data, ['moneyTransactionShort']);

            return $this->redirect($this->generateUrl('money_out_short_add_another', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_out_short_summary' : 'money_out_short_one_off_payments_exist', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/add_another", name="money_out_short_add_another")
     *
     * @Template("@App/Report/MoneyOutShort/addAnother.html.twig")
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
                    return $this->redirectToRoute('money_out_short_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/edit/{transactionId}", name="money_out_short_edit")
     *
     * @Template("@App/Report/MoneyOutShort/edit.html.twig")
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

            return $this->redirect($this->generateUrl('money_out_short_summary', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('money_out_short_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/{transactionId}/delete", name="money_out_short_delete")
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
            $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

            $this->restClient->delete('report/'.$report->getId().'/money-transaction-short/'.$transactionId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Entry deleted'
            );

            return $this->redirect($this->generateUrl('money_out_short_summary', ['reportId' => $reportId]));
        }

        $transaction = $this->restClient->get('report/'.$report->getId().'/money-transaction-short/'.$transactionId, 'Report\MoneyTransactionShort');

        return [
            'translationDomain' => 'report-money-out',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.description', 'value' => $transaction->getDescription()],
                ['label' => 'deletePage.summary.date', 'value' => $transaction->getDate(), 'format' => 'date'],
                ['label' => 'deletePage.summary.amount', 'value' => $transaction->getAmount(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('money_out_short_summary', ['reportId' => $reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/summary", name="money_out_short_summary")
     *
     * @Template("@App/Report/MoneyOutShort/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyOutShortState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('money_out_short', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'moneyOutShort';
    }
}
