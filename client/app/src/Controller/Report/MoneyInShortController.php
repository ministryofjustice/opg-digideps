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

class MoneyInShortController extends AbstractController
{
    private static $jmsGroups = [
        'moneyShortCategoriesIn',
        'moneyTransactionsShortIn',
        'money-in-short-state',
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
     * @Route("/report/{reportId}/money-in-short", name="money_in_short")
     * @Template("@App/Report/MoneyInShort/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getMoneyInShortState()['state']) {
            return $this->redirectToRoute('money_in_short_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/category", name="money_in_short_category")
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

            return $this->redirectToRoute('money_in_short_exist', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_in_short_summary' : 'money_in_short', ['reportId' => $reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/exist", name="money_in_short_exist")
     * @Template("@App/Report/MoneyInShort/exist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            ['field' => 'moneyTransactionsShortInExist', 'translation_domain' => 'report-money-short']
        );
        $form->handleRequest($request);
        $fromSummaryPage = 'summary' == $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            $this->restClient->put('report/'.$reportId, $data, ['money-transactions-short-in-exist']);
            switch ($data->getMoneyTransactionsShortInExist()) {
                case 'yes':
                    return $this->redirectToRoute('money_in_short_add', ['reportId' => $reportId, 'from' => 'exist']);
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
     * @Template("@App/Report/MoneyInShort/add.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $record = new MoneyTransactionShort('in');
        $record->setReport($report);

        $form = $this->createForm(FormDir\Report\MoneyShortTransactionType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->post('report/'.$report->getId().'/money-transaction-short', $data, ['moneyTransactionShort']);

            return $this->redirect($this->generateUrl('money_in_short_add_another', ['reportId' => $reportId]));
        }

        try {
            $backLinkRoute = 'money_in_short_'.$request->get('from');
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
     * @Route("/report/{reportId}/money-in-short/add_another", name="money_in_short_add_another")
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
     * @Template("@App/Report/MoneyInShort/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyInShortState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('money_in_short', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/new_summary", name="money_in_short_new_summary")
     * @Template("@App/Report/MoneyInShort/new_summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function newSummaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyInShortState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('money_in_short', ['reportId' => $reportId]);
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
        return 'moneyInShort';
    }
}
