<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\MoneyTransactionShort;
use AppBundle\Entity\Report\Report;
use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use AppBundle\Service\SectionValidator\Odr\IncomeBenefitsValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class MoneyInShortController extends AbstractController
{
    private static $jmsGroups = [
        'client-cot',
        'moneyShortCategoriesIn',
        'moneyTransactionsShortIn',
    ];

    /**
     * @Route("/report/{reportId}/money-in-short", name="money_in_short")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getMoneyTransactionsShortInExist() || $report->getMoneyShortCategoriesInPresent()) {
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
        $fromPage = $request->get('from');

        $form = $this->createForm(new FormDir\Report\MoneyShortType(), $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $data, ['moneyShortCategoriesIn']);

            if ($fromPage == 'summary') {
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
            'backLink' => $this->generateUrl($fromPage == 'summary' ? 'money_in_short_summary' : 'money_in_short', ['reportId'=>$reportId]), //FIX when from summary
            'skipLink' => $this->generateUrl('money_in_short_exist', ['reportId'=>$reportId]), //FIX when from summary
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
        $fromPage = $request->get('from');

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data Report */
            switch ($data->getMoneyTransactionsShortInExist()) {
                case 'yes':
                    return $this->redirectToRoute('money_in_short_add', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('report/' . $reportId, $data, ['money-transactions-short-in-exist']);
                    return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => $this->generateUrl($fromPage == 'summary' ? 'money_in_short_summary' : 'money_in_short_category', ['reportId'=>$reportId]), //FIX when from summary
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
            $request->getSession()->getFlashBag()->add('notice', 'Record edited');

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
            'Record deleted'
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

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report' => $report,
        ];
    }
}
