<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DeputyExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'expenses',
    ];

    /**
     * @Route("/report/{reportId}/deputy-expenses", name="deputy_expenses")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (count($report->getExpenses()) > 0 || $report->getPaidForAnything() !== null) {
            return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/exist", name="deputy_expenses_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\DeputyExpenseExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('deputy_expenses_add', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('report/'.$reportId, $data, ['expenses-paid-anything']);
                    return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('deputy_expenses', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('deputy_expenses_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/add", name="deputy_expenses_add")
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = new EntityDir\Report\Expense();

        $form = $this->createForm(new FormDir\Report\DeputyExpenseType(), $expense);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->post('report/'.$report->getId().'/expense', $data, ['expense']);

            return $this->redirect($this->generateUrl('deputy_expenses_add_another', ['reportId' => $reportId]));
        }

        $backLinkRoute = 'deputy_expenses_'.$request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['reportId'=>$reportId]) : '';


        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/add_another", name="deputy_expenses_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\AddAnotherRecordType('report-deputy-expenses'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('deputy_expenses_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/edit/{expenseId}", name="deputy_expenses_edit")
     * @Template()
     */
    public function editAction(Request $request, $reportId, $expenseId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = $this->getRestClient()->get('report/'.$report->getId().'/expense/'.$expenseId, 'Report\Expense');

        $form = $this->createForm(new FormDir\Report\DeputyExpenseType(), $expense);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Record edited');

            $this->getRestClient()->put('report/'.$report->getId().'/expense/'.$expense->getId(), $data, ['expense']);

            return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('deputy_expenses_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/summary", name="deputy_expenses_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (count($report->getExpenses()) === 0 && $report->getPaidForAnything() === null) {
            return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/{expenseId}/delete", name="deputy_expenses_delete")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $expenseId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $this->getRestClient()->delete('report/'.$report->getId().'/expense/'.$expenseId);

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Expense deleted'
        );

        return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
    }
}
