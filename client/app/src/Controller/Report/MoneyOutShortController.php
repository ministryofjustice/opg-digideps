<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\Report\MoneyTransactionShort;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class MoneyOutShortController extends AbstractController
{
    private static $jmsGroups = [
        'moneyShortCategoriesOut',
        'moneyTransactionsShortOut',
        'money-out-short-state',
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
    }

    /**
     * @Route("/report/{reportId}/money-out-short", name="money_out_short")
     * @Template("@App/Report/MoneyOutShort/start.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getMoneyOutShortState()['state']) {
            return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/category", name="money_out_short_category")
     * @Template("@App/Report/MoneyOutShort/category.html.twig")
     *
     * @
     * param $reportId
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

            return $this->redirectToRoute('money_out_short_exist', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_out_short_summary' : 'money_out_short', ['reportId' => $reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/exist", name="money_out_short_exist")
     * @Template("@App/Report/MoneyOutShort/exist.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            ['field' => 'moneyTransactionsShortOutExist', 'translation_domain' => 'report-money-short']
        );
        $form->handleRequest($request);
        $fromSummaryPage = 'summary' == $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            $this->restClient->put('report/'.$reportId, $data, ['money-transactions-short-out-exist']);
            switch ($data->getMoneyTransactionsShortOutExist()) {
                case 'yes':
                    return $this->redirectToRoute('money_out_short_add', ['reportId' => $reportId, 'from' => 'exist']);
                case 'no':
                    return $this->redirectToRoute('money_out_short_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_out_short_summary' : 'money_out_short_category', ['reportId' => $reportId]), //FIX when from summary
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out-short/add", name="money_out_short_add")
     * @Template("@App/Report/MoneyOutShort/add.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $record = new MoneyTransactionShort('out');
        $record->setReport($report);

        $form = $this->createForm(FormDir\Report\MoneyShortTransactionType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->post('report/'.$report->getId().'/money-transaction-short', $data, ['moneyTransactionShort']);

            return $this->redirect($this->generateUrl('money_out_short_add_another', ['reportId' => $reportId]));
        }

        try {
            $backLinkRoute = 'money_out_short_'.$request->get('from');
            $backLink = $this->generateUrl($backLinkRoute, ['reportId' => $reportId]);

            return [
                'backLink' => $backLink,
                'form' => $form->createView(),
                'report' => $report,
            ];
        } catch (RouteNotFoundException $e) {
            return [
                'backLink' => null,
                'form' => $form->createView(),
                'report' => $report,
            ];
        }
    }

    /**
     * @Route("/report/{reportId}/money-out-short/add_another", name="money_out_short_add_another")
     * @Template("@App/Report/MoneyOutShort/addAnother.html.twig")
     *
     * @param $reportId
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
     * @Template("@App/Report/MoneyOutShort/edit.html.twig")
     *
     * @param $reportId
     * @param $transactionId
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
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @param $reportId
     * @param $transactionId
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
     * @Template("@App/Report/MoneyOutShort/summary.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyOutShortState()['state'] && 'skip-step' != $fromPage) {
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
