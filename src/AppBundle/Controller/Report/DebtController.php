<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DebtController extends AbstractController
{
    private static $jmsGroups = ['debt'];

    /**
     * @Route("/report/{reportId}/debts", name="debts")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getHasDebts() != null) {
            return $this->redirectToRoute('debts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/debts/exist", name="debts_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\YesNoType('hasDebts', 'report-debts'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('report/'.$reportId, $report, ['debt']);

            if ($report->getHasDebts() == 'yes') {
                return $this->redirectToRoute('debts_edit', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('debts_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('debts', ['reportId'=>$reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('debts_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * List debts.
     *
     * @Route("/report/{reportId}/debts/edit", name="debts_edit")
     * @Template()
     */
    public function editAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\DebtsType(), $report);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isValid()) {
            $this->getRestClient()->put('report/'.$report->getId(), $form->getData(), ['debt']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Debt edited');
            }

            return $this->redirect($this->generateUrl('debts_summary', ['reportId' => $reportId]));
        }

        $backLink = $this->generateUrl('debts_exist', ['reportId'=>$reportId]);
        if ($fromPage == 'summary') {
            $backLink = $this->generateUrl('debts_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * List debts.
     *
     * @Route("/report/{reportId}/debts/summary", name="debts_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getHasDebts() == null) {
            return $this->redirectToRoute('debts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }
}
