<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use Doctrine\Common\Util\Debug;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DebtController extends AbstractController
{
    /**
     * List debts
     *
     * @Route("/report/{reportId}/debts", name="debts")
     * @Template("AppBundle:Debt:list.html.twig")
     */
    public function listAction(Request $request, $reportId)
    {
        $report = $this->getReport($reportId, ['debts', 'basic', 'client']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }

        $form = $this->createForm(new FormDir\DebtsType, $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('restClient')->put('report/' . $report->getId(), $form->getData(), [
                'deserialise_group' => 'debts',
            ]);

            return $this->redirect($this->generateUrl('debts', ['reportId' => $reportId]));
        } else if ($form->isSubmitted()) {
            //echo $form->getErrorsAsString();
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
        $report = $this->getReport($reportId, ['debts', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }

        $form = $this->createForm(new FormDir\DebtsType, $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('restClient')->put('report/' . $report->getId(), $form->getData(), [
                'deserialise_group' => 'debts',
            ]);

            return JsonResponse(['success' => true]);
        }

        return JsonResponse([
            'false' => true,
            'message' => (String)$form->getErrors()
        ]);

    }
}
