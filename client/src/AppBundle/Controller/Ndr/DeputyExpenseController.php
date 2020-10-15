<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\NdrStatusService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class DeputyExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'ndr-expenses',
    ];

    /**
     * @var ReportApi
     */
    private $reportApi;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(
        ReportApi $reportApi,
        RestClient $restClient
    )
    {
        $this->reportApi = $reportApi;
        $this->restClient = $restClient;
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses", name="ndr_deputy_expenses")
     * @Template("AppBundle:Ndr/DeputyExpense:start.html.twig")
     *
     * @param $ndrId
     *
     * @return array|RedirectResponse
     */
    public function startAction($ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        if ($ndr->getStatusService()->getExpensesState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_deputy_expenses_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/exist", name="ndr_deputy_expenses_exist")
     * @Template("AppBundle:Ndr/DeputyExpense:exist.html.twig")
     */
    public function existAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $ndr, [ 'field' => 'paidForAnything', 'translation_domain' => 'ndr-deputy-expenses']
                                 );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Ndr\Ndr */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('ndr_deputy_expenses_add', ['ndrId' => $ndrId, 'from'=>'exist']);
                case 'no':
                    $this->restClient->put('ndr/' . $ndrId, $data, ['ndr-expenses-paid-anything']);
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
     * @Template("AppBundle:Ndr/DeputyExpense:add.html.twig")
     *
     * @param Request $request
     * @param $ndrId
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $expense = new EntityDir\Ndr\Expense();

        $form = $this->createForm(FormDir\Ndr\DeputyExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setNdr($ndr);

            $this->restClient->post('ndr/' . $ndr->getId() . '/expense', $data, ['ndr-expense']);

            return $this->redirect($this->generateUrl('ndr_deputy_expenses_add_another', ['ndrId' => $ndrId]));
        }

        try {
            $backLinkRoute = 'ndr_deputy_expenses_' . $request->get('from');
            $backLink = $this->generateUrl($backLinkRoute,  ['ndrId'=>$ndrId]);

            return [
                'backLink' => $backLink,
                'form' => $form->createView(),
                'ndr' => $ndr,
            ];
        } catch (RouteNotFoundException $e) {
            return [
                'backLink' => null,
                'form' => $form->createView(),
                'ndr' => $ndr,
            ];
        }
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/add_another", name="ndr_deputy_expenses_add_another")
     * @Template("AppBundle:Ndr/DeputyExpense:addAnother.html.twig")
     */
    public function addAnotherAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $ndr, ['translation_domain' => 'ndr-deputy-expenses']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * @Template("AppBundle:Ndr/DeputyExpense:edit.html.twig")
     */
    public function editAction(Request $request, $ndrId, $expenseId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $expense = $this->restClient->get('ndr/' . $ndr->getId() . '/expense/' . $expenseId, 'Ndr\Expense');

        $form = $this->createForm(FormDir\Ndr\DeputyExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Expense edited');

            $this->restClient->put('ndr/' . $ndr->getId() . '/expense/' . $expense->getId(), $data, ['ndr-expense']);

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
     * @Template("AppBundle:Ndr/DeputyExpense:summary.html.twig")
     *
     * @param $ndrId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getExpensesState()['state'] == NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]));
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/{expenseId}/delete", name="ndr_deputy_expenses_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param int $id
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $ndrId, $expenseId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

            $this->restClient->delete('ndr/' . $ndr->getId() . '/expense/' . $expenseId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Expense deleted'
            );

            return $this->redirect($this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]));
        }

        $expense = $this->restClient->get('ndr/' . $ndrId . '/expense/' . $expenseId, 'Ndr\Expense');

        return [
            'translationDomain' => 'ndr-deputy-expenses',
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.explanation', 'value' => $expense->getExplanation()],
                ['label' => 'deletePage.summary.amount', 'value' => $expense->getAmount(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]),
        ];
    }
}
