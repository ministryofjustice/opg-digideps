<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\MoneyTransactionShort;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class MoneyInShortController extends AbstractController
{
    private static $jmsGroups = [
        'moneyShortCategoriesIn',
        'moneyTransactionsShortIn',
        'money-in-short-state',
    ];

    /**
     * @Route("/report/{reportId}/money-in-short", name="money_in_short")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getMoneyInShortState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/category", name="money_in_short_category")
     * @Template()
     */
    public function categoryAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = $request->get('from') == 'summary';

        $form = $this->createForm(new FormDir\Report\MoneyShortType('moneyShortCategoriesIn'), $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $data, ['moneyShortCategoriesIn']);

            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );

                return $this->redirectToRoute('money_in_short_summary', ['reportId'=>$reportId]);
            }

            return $this->redirectToRoute('money_in_short_exist', ['reportId'=>$reportId]);
        }


        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_in_short_summary' : 'money_in_short', ['reportId'=>$reportId]),
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('money_in_short_exist', ['reportId'=>$reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/exist", name="money_in_short_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\YesNoType('moneyTransactionsShortInExist', 'report-money-short', ['yes' => 'Yes', 'no' => 'No']), $report);
        $form->handleRequest($request);
        $fromSummaryPage = $request->get('from') == 'summary';

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            $this->getRestClient()->put('report/' . $reportId, $data, ['money-transactions-short-in-exist']);
            switch ($data->getMoneyTransactionsShortInExist()) {
                case 'yes':
                    return $this->redirectToRoute('money_in_short_add', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_in_short_summary' : 'money_in_short_category', ['reportId'=>$reportId]), //FIX when from summary
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/add", name="money_in_short_add")
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $record = new MoneyTransactionShort('in');
        $record->setReport($report);

        $form = $this->createForm(new FormDir\Report\MoneyShortTransactionType(), $record);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->post('report/' . $report->getId() . '/money-transaction-short', $data, ['moneyTransactionShort']);

            return $this->redirect($this->generateUrl('money_in_short_add_another', ['reportId' => $reportId]));
        }

        $backLinkRoute = 'money_in_short_' . $request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['reportId'=>$reportId]) : '';


        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/add_another", name="money_in_short_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\AddAnotherRecordType('report-money-short'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
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
     * @Template()
     */
    public function editAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $transaction = $this->getRestClient()->get('report/' . $report->getId() . '/money-transaction-short/' . $transactionId, 'Report\MoneyTransactionShort');

        $form = $this->createForm(new FormDir\Report\MoneyShortTransactionType(), $transaction);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Entry edited');

            $this->getRestClient()->put('report/' . $report->getId() . '/money-transaction-short/' . $transaction->getId(), $data, ['moneyTransactionShort']);

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
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $this->getRestClient()->delete('report/' . $report->getId() . '/money-transaction-short/' . $transactionId);

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Entry deleted'
        );

        return $this->redirect($this->generateUrl('money_in_short_summary', ['reportId' => $reportId]));
    }

    /**
     * @Route("/report/{reportId}/money-in-short/summary", name="money_in_short_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getMoneyInShortState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('money_in_short', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report' => $report,
        ];
    }
}
