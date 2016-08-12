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
     * List debts.
     *
     * @Route("/report/{reportId}/debts", name="debts")
     * @Template("AppBundle:Report/Debt:list.html.twig")
     */
    public function listAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['debt']);
        $form = $this->createForm(new FormDir\Report\DebtsType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('restClient')->put('report/'.$report->getId(), $form->getData(), [
                'debt',
            ]);

            return $this->redirect($this->generateUrl('debts', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/debts", name="debts_save_json")
     * @Method("PUT")
     */
    public function debtSaveJsonAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['debt']);
        $form = $this->createForm(new FormDir\Report\DebtsType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('restClient')->put('report/'.$report->getId(), $form->getData(), [
                'debt',
            ]);

            return JsonResponse(['success' => true]);
        }

        return JsonResponse([
            'false' => true,
            'message' => (String) $form->getErrors(),
        ]);
    }
}
