<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class BalanceController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/accounts/balance", name="accounts_balance")
     *
     * @param int $reportId
     * @Template()
     *
     * @return array
     */
    public function balanceAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['balance', 'account', 'transaction']);
        $form = $this->createForm(new FormDir\Report\ReasonForBalanceType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->put('report/'.$reportId, $data, ['balance_mismatch_explanation']);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'subsection' => 'balance',
        ];
    }
}
