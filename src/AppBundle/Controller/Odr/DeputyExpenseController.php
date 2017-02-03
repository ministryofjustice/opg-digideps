<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DeputyExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'odr-expenses',
    ];

    /**
     * @Route("/odr/{odrId}/deputy-expenses", name="odr_deputy_expenses")
     * @Template()
     *
     * @param int $odrId
     *
     * @return array
     */
    public function startAction($odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);

        if ((new OdrStatusService($odr))->getExpensesState()['state'] != OdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('odr_deputy_expenses_summary', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/deputy-expenses/exist", name="odr_deputy_expenses_exist")
     * @Template()
     */
    public function existAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\YesNoType('paidForAnything', 'odr-deputy-expenses', ['yes' => 'Yes', 'no' => 'No']), $odr);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Odr\Odr */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('odr_deputy_expenses_add', ['odrId' => $odrId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('odr/' . $odrId, $data, ['odr-expenses-paid-anything']);
                    return $this->redirectToRoute('odr_deputy_expenses_summary', ['odrId' => $odrId]);
            }
        }

        $backLink = $this->generateUrl('odr_deputy_expenses', ['odrId' => $odrId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('odr_deputy_expenses_summary', ['odrId' => $odrId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/deputy-expenses/add", name="odr_deputy_expenses_add")
     * @Template()
     */
    public function addAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $expense = new EntityDir\Odr\Expense();

        $form = $this->createForm(new FormDir\Odr\DeputyExpenseType(), $expense);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setOdr($odr);

            $this->getRestClient()->post('odr/' . $odr->getId() . '/expense', $data, ['odr-expense']);

            return $this->redirect($this->generateUrl('odr_deputy_expenses_add_another', ['odrId' => $odrId]));
        }

        $backLinkRoute = 'odr_deputy_expenses_' . $request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['odrId'=>$odrId]) : '';


        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/deputy-expenses/add_another", name="odr_deputy_expenses_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\AddAnotherRecordType('odr-deputy-expenses'), $odr);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('odr_deputy_expenses_add', ['odrId' => $odrId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('odr_deputy_expenses_summary', ['odrId' => $odrId]);
            }
        }

        return [
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/deputy-expenses/edit/{expenseId}", name="odr_deputy_expenses_edit")
     * @Template()
     */
    public function editAction(Request $request, $odrId, $expenseId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $expense = $this->getRestClient()->get('odr/' . $odr->getId() . '/expense/' . $expenseId, 'Odr\Expense');

        $form = $this->createForm(new FormDir\Odr\DeputyExpenseType(), $expense);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Expense edited');

            $this->getRestClient()->put('odr/' . $odr->getId() . '/expense/' . $expense->getId(), $data, ['odr-expense']);

            return $this->redirect($this->generateUrl('odr_deputy_expenses', ['odrId' => $odrId]));
        }

        return [
            'backLink' => $this->generateUrl('odr_deputy_expenses_summary', ['odrId' => $odrId]),
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/deputy-expenses/summary", name="odr_deputy_expenses_summary")
     * @Template()
     *
     * @param int $odrId
     *
     * @return array
     */
    public function summaryAction($odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        if ((new OdrStatusService($odr))->getExpensesState()['state'] == OdrStatusService::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('odr_deputy_expenses', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/deputy-expenses/{expenseId}/delete", name="odr_deputy_expenses_delete")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $odrId, $expenseId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);

        $this->getRestClient()->delete('odr/' . $odr->getId() . '/expense/' . $expenseId);

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Expense deleted'
        );

        return $this->redirect($this->generateUrl('odr_deputy_expenses', ['odrId' => $odrId]));
    }
}
