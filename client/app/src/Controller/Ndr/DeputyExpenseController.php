<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class DeputyExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'ndr-expenses',
    ];

    /** @var ReportApi */
    private $reportApi;

    /** @var RestClient */
    private $restClient;

    /** @var RouterInterface */
    private $router;
    private $clientApi;

    public function __construct(
        ReportApi $reportApi,
        RestClient $restClient,
        RouterInterface $router,
        ClientApi $clientApi
    ) {
        $this->reportApi = $reportApi;
        $this->restClient = $restClient;
        $this->router = $router;
        $this->clientApi = $clientApi;
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses", name="ndr_deputy_expenses")
     *
     * @Template("@App/Ndr/DeputyExpense/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction($ndrId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        if (NdrStatusService::STATE_NOT_STARTED != $ndr->getStatusService()->getExpensesState()['state']) {
            return $this->redirectToRoute('ndr_deputy_expenses_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/exist", name="ndr_deputy_expenses_exist")
     *
     * @Template("@App/Ndr/DeputyExpense/exist.html.twig")
     */
    public function existAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $ndr,
            ['field' => 'paidForAnything', 'translation_domain' => 'ndr-deputy-expenses']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Ndr\Ndr */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('ndr_deputy_expenses_add', ['ndrId' => $ndrId, 'from' => 'exist']);
                case 'no':
                    $this->restClient->put('ndr/'.$ndrId, $data, ['ndr-expenses-paid-anything']);

                    return $this->redirectToRoute('ndr_deputy_expenses_summary', ['ndrId' => $ndrId]);
            }
        }

        $backLink = $this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]);
        if ('summary' == $request->get('from')) {
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
     *
     * @Template("@App/Ndr/DeputyExpense/add.html.twig")
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

            $this->restClient->post('ndr/'.$ndr->getId().'/expense', $data, ['ndr-expense']);

            return $this->redirect($this->generateUrl('ndr_deputy_expenses_add_another', ['ndrId' => $ndrId]));
        }

        try {
            $backLinkRoute = 'ndr_deputy_expenses_'.$request->get('from');
            $backLink = $this->router->generate($backLinkRoute, ['ndrId' => $ndrId]);

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
     *
     * @Template("@App/Ndr/DeputyExpense/addAnother.html.twig")
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
     *
     * @Template("@App/Ndr/DeputyExpense/edit.html.twig")
     */
    public function editAction(Request $request, $ndrId, $expenseId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $expense = $this->restClient->get('ndr/'.$ndr->getId().'/expense/'.$expenseId, 'Ndr\Expense');

        $form = $this->createForm(FormDir\Ndr\DeputyExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Expense edited');

            $this->restClient->put('ndr/'.$ndr->getId().'/expense/'.$expense->getId(), $data, ['ndr-expense']);

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
     *
     * @Template("@App/Ndr/DeputyExpense/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED == $ndr->getStatusService()->getExpensesState()['state']) {
            return $this->redirect($this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]));
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputy-expenses/{expenseId}/delete", name="ndr_deputy_expenses_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $ndrId, $expenseId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

            $this->restClient->delete('ndr/'.$ndr->getId().'/expense/'.$expenseId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Expense deleted'
            );

            return $this->redirect($this->generateUrl('ndr_deputy_expenses', ['ndrId' => $ndrId]));
        }

        $expense = $this->restClient->get('ndr/'.$ndrId.'/expense/'.$expenseId, 'Ndr\Expense');

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
