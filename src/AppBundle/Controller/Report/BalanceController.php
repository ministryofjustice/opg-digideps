<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BalanceController extends AbstractController
{
    private static $jmsGroups = [
        'balance',
        'balance-state',
    ];

    /**
     * @Route("/report/{reportId}/balance", name="balance")
     *
     * @param int $reportId
     * @Template()
     *
     * @return array
     */
    public function balanceAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\ReasonForBalanceType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->put('report/' . $reportId, $data, ['balance_mismatch_explanation']);

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
}
