<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BalanceController extends AbstractController
{
    private static $jmsGroups = [
        'report',
        'account',
        'expenses',
        'fee',
        'gifts',
        'report-prof-deputy-costs',
        'debt',
        'fee',
        'balance',
        'debts',
        'transaction',
        'transactionsIn',
        'transactionsOut',
        'moneyTransactionsShortIn',
        'moneyTransactionsShortOut',
        'status',
        'balance',
        'balance-state',
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
     * @Route("/report/{reportId}/balance", name="balance")
     *
     * @param $reportId
     * @Template("App:Report/Balance:balance.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function balanceAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\ReasonForBalanceType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->put('report/' . $reportId, $data, ['balance_mismatch_explanation']);

//            $request->getSession()->getFlashBag()->add(
//                'notice',
//                'Balance explanation added'
//            );

            return $this->redirectToRoute('report_overview', ['reportId'=>$report->getId()]);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('report_overview', ['reportId'=>$report->getId()])
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'balance';
    }
}
