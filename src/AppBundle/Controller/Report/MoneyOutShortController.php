<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\MoneyTransactionShort;
use AppBundle\Entity\Report\Report;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class MoneyOutShortController extends AbstractController
{
    private static $jmsGroups = [
        'moneyShortCategoriesOut',
        'moneyTransactionsShortOut',
    ];

    /**
     * @Route("/report/{reportId}/money-out-short", name="money_out_short")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups, Report::TYPE_103);

        if ((new ReportStatusService($report))->getMoneyOutShortState()['state'] != ReportStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/category", name="money_out_short_category")
     * @Template()
     */
    public function categoryAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups, Report::TYPE_103);
        $fromSummaryPage = $request->get('from') == 'summary';

        $form = $this->createForm(new FormDir\Report\MoneyShortType('moneyShortCategoriesOut'), $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $data, ['moneyShortCategoriesOut']);

            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );

                return $this->redirectToRoute('money_out_short_summary', ['reportId'=>$reportId]);
            }

            return $this->redirectToRoute('money_out_short_exist', ['reportId'=>$reportId]);
        }


        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_out_short_summary' : 'money_out_short', ['reportId'=>$reportId]),
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('money_out_short_exist', ['reportId'=>$reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/exist", name="money_out_short_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups, Report::TYPE_103);
        $form = $this->createForm(new FormDir\YesNoType('moneyTransactionsShortOutExist', 'report-money-short', ['yes' => 'Yes', 'no' => 'No']), $report);
        $form->handleRequest($request);
        $fromSummaryPage = $request->get('from') == 'summary';

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data Report */
            $this->getRestClient()->put('report/' . $reportId, $data, ['money-transactions-short-out-exist']);
            switch ($data->getMoneyTransactionsShortOutExist()) {
                case 'yes':
                    return $this->redirectToRoute('money_out_short_add', ['reportId' => $reportId, 'from'=>'exist']);
                    break;
                case 'no':
                    return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_out_short_summary' : 'money_out_short_category', ['reportId'=>$reportId]), //FIX when from summary
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/add", name="money_out_short_add")
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups, Report::TYPE_103);
        $record = new MoneyTransactionShort('out');
        $record->setReport($report);

        $form = $this->createForm(new FormDir\Report\MoneyShortTransactionType(), $record);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->post('report/' . $report->getId() . '/money-transaction-short', $data, ['moneyTransactionShort']);

            return $this->redirect($this->generateUrl('money_out_short_add_another', ['reportId' => $reportId]));
        }

        $backLinkRoute = 'money_out_short_' . $request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['reportId'=>$reportId]) : '';


        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/add_another", name="money_out_short_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups, Report::TYPE_103);

        $form = $this->createForm(new FormDir\AddAnotherRecordType('report-money-short'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
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
     * @Template()
     */
    public function editAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups, Report::TYPE_103);
        $transaction = $this->getRestClient()->get('report/' . $report->getId() . '/money-transaction-short/' . $transactionId, 'Report\MoneyTransactionShort');

        $form = $this->createForm(new FormDir\Report\MoneyShortTransactionType(), $transaction);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Entry edited');

            $this->getRestClient()->put('report/' . $report->getId() . '/money-transaction-short/' . $transaction->getId(), $data, ['moneyTransactionShort']);

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
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups, Report::TYPE_103);

        $this->getRestClient()->delete('report/' . $report->getId() . '/money-transaction-short/' . $transactionId);

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Entry deleted'
        );

        return $this->redirect($this->generateUrl('money_out_short_summary', ['reportId' => $reportId]));
    }

    /**
     * @Route("/report/{reportId}/money-out-short/summary", name="money_out_short_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups, Report::TYPE_103);
        if ((new ReportStatusService($report))->getMoneyOutShortState()['state'] == ReportStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('money_out_short', ['reportId' => $reportId]);
        }


        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report' => $report,
        ];
    }
}
