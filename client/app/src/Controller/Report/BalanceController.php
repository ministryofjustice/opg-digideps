<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
        private ClientApi $clientApi,
    ) {
    }

    /**
     * @Route("/report/{reportId}/balance", name="balance")
     *
     * @Template("@App/Report/Balance/balance.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function balanceAction(Request $request, $reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = $this->clientApi->checkDeputyHasMultiClients($user);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\ReasonForBalanceType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->put('report/'.$reportId, $data, ['balance_mismatch_explanation']);

            return $this->redirectToRoute('report_overview', ['reportId' => $report->getId()]);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('report_overview', ['reportId' => $report->getId()]),
            'isMultiClientDeputy' => $isMultiClientDeputy,
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
