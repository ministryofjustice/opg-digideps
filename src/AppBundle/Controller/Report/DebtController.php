<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DebtController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/debts/start", name="debts")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['debt']);
        if($report->getHasDebts() != null) {
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
        $report = $this->getReportIfReportNotSubmitted($reportId, ['debt']);
        // if contacts are already added, skip to next step (=add)
//        if($report->getHasDebts() != null) {
//            return $this->redirectToRoute('debt_Start', ['reportId' => $reportId]);
//        }

        $form = $this->createForm(new FormDir\Report\DebtsExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['exist']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('debts_edit', ['reportId' => $reportId]);
                case 'no':
                    $this->get('restClient')->put('report/' . $reportId, ['has_debts'=>'no', 'debts'=>[]]);
                    return $this->redirectToRoute('debts_summary', ['reportId' => $reportId]);
            }
        }

        return [
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
        $report = $this->getReportIfReportNotSubmitted($reportId, ['debt']);
        $form = $this->createForm(new FormDir\Report\DebtsType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('restClient')->put('report/'.$report->getId(), $form->getData(), ['debt']);

            return $this->redirect($this->generateUrl('debts_summary', ['reportId' => $reportId]));
        }

        return [
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
        $report = $this->getReportIfReportNotSubmitted($reportId, ['debt']);
        $form = $this->createForm(new FormDir\Report\DebtsType(), $report);
        $form->handleRequest($request);

        return [
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/debts", name="debts_save_json")
     * @Method("PUT")
     */
//    public function debtSaveJsonAction(Request $request, $reportId)
//    {
//        $report = $this->getReportIfReportNotSubmitted($reportId, ['debt']);
//        $form = $this->createForm(new FormDir\Report\DebtsType(), $report);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $this->get('restClient')->put('report/'.$report->getId(), $form->getData(), ['debt']);
//
//            return JsonResponse(['success' => true]);
//        }
//
//        return JsonResponse([
//            'false' => true,
//            'message' => (String) $form->getErrors(),
//        ]);
//    }
}
