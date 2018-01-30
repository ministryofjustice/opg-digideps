<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\NdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DeputyExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'ndr-expenses',
    ];

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses", name="ndr_deputy_expenses")
     * @Template()
     *
     * @param int $ndrId
     *
     * @return array
     */
    public function startAction($ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        if ($ndr->getStatusService()->getExpensesState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_deputy_expenses_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/exist", name="ndr_deputy_expenses_exist")
     * @Template()
     */
    public function existAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $ndr, [ 'field' => 'paidForAnything', 'translation_domain' => 'ndr-deputy-expenses']
                                 );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Ndr\Ndr */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('ndr_deputy_expenses_add', ['ndrId' => $ndrId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('ndr/' . $ndrId, $data, ['ndr-expenses-paid-anything']);
                    return $this->redirectToRoute('ndr_deputy_expenses_summary', ['ndrId' => $ndrId]);
            }
        }

        $backLink = $this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('ndr_deputy_expenses_summary', ['ndrId' => $ndrId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/add", name="ndr_deputy_expenses_add")
     * @Template()
     */
    public function addAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $expense = new EntityDir\Ndr\Expense();

        $form = $this->createForm(FormDir\Ndr\DeputyExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setNdr($ndr);

            $this->getRestClient()->post('ndr/' . $ndr->getId() . '/expense', $data, ['ndr-expense']);

            return $this->redirect($this->generateUrl('ndr_deputy_expenses_add_another', ['ndrId' => $ndrId]));
        }

        $backLinkRoute = 'ndr_deputy_expenses_' . $request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['ndrId'=>$ndrId]) : '';


        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/add_another", name="ndr_deputy_expenses_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $ndr, ['translation_domain' => 'ndr-deputy-expenses']);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('ndr_deputy_expenses_add', ['ndrId' => $ndrId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('ndr_deputy_expenses_summary', ['ndrId' => $ndrId]);
            }
        }

        return [
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/edit/{expenseId}", name="ndr_deputy_expenses_edit")
     * @Template()
     */
    public function editAction(Request $request, $ndrId, $expenseId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $expense = $this->getRestClient()->get('ndr/' . $ndr->getId() . '/expense/' . $expenseId, 'Ndr\Expense');

        $form = $this->createForm(FormDir\Ndr\DeputyExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Expense edited');

            $this->getRestClient()->put('ndr/' . $ndr->getId() . '/expense/' . $expense->getId(), $data, ['ndr-expense']);

            return $this->redirect($this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]));
        }

        return [
            'backLink' => $this->generateUrl('ndr_deputy_expenses_summary', ['ndrId' => $ndrId]),
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/summary", name="ndr_deputy_expenses_summary")
     * @Template()
     *
     * @param int $ndrId
     *
     * @return array
     */
    public function summaryAction($ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getExpensesState()['state'] == NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]));
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/{expenseId}/delete", name="ndr_deputy_expenses_delete")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $ndrId, $expenseId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        $this->getRestClient()->delete('ndr/' . $ndr->getId() . '/expense/' . $expenseId);

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Expense deleted'
        );

        return $this->redirect($this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]));
    }
}
