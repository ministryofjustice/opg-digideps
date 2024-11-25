<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DebtController extends AbstractController
{
    private static $jmsGroups = ['ndr-debt', 'ndr-debt-management'];

    /**
     * @var ReportApi
     */
    private $reportApi;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var ClientApi
     */
    private $clientApi;

    public function __construct(
        ReportApi $reportApi,
        RestClient $restClient,
        ClientApi $clientApi
    ) {
        $this->reportApi = $reportApi;
        $this->restClient = $restClient;
        $this->clientApi = $clientApi;
    }

    /**
     * @Route("/ndr/{ndrId}/debts", name="ndr_debts")
     *
     * @Template("@App/Ndr/Debt/start.html.twig")
     */
    public function startAction(Request $request, $ndrId)
    {
        /** @var User $user */
        $user = $this->getUser();

        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED != $ndr->getStatusService()->getDebtsState()['state']) {
            return $this->redirectToRoute('ndr_debts_summary', ['ndrId' => $ndr->getId()]);
        }

        return [
            'ndr' => $ndr,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/debts/exist", name="ndr_debts_exist")
     *
     * @Template("@App/Ndr/Debt/exist.html.twig")
     */
    public function existAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $ndr,
            ['field' => 'hasDebts', 'translation_domain' => 'ndr-debts']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('ndr/'.$ndrId, $ndr, ['debt']);

            if ('yes' == $ndr->getHasDebts()) {
                return $this->redirectToRoute('ndr_debts_edit', ['ndrId' => $ndrId]);
            }

            return $this->redirectToRoute('ndr_debts_summary', ['ndrId' => $ndrId]);
        }

        $backLink = $this->generateUrl('ndr_debts', ['ndrId' => $ndrId]);
        if ('summary' == $request->get('from')) {
            $backLink = $this->generateUrl('ndr_debts_summary', ['ndrId' => $ndrId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'ndr' => $ndr,
        ];
    }

    /**
     * List debts.
     *
     * @Route("/ndr/{ndrId}/debts/edit", name="ndr_debts_edit")
     *
     * @Template("@App/Ndr/Debt/edit.html.twig")
     */
    public function editAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Ndr\Debt\DebtsType::class, $ndr);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('ndr/'.$ndr->getId(), $form->getData(), ['debt']);

            if ('summary' == $fromPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Debt edited');

                return $this->redirect($this->generateUrl('ndr_debts_summary', ['ndrId' => $ndrId]));
            }

            return $this->redirect($this->generateUrl('ndr_debts_management', ['ndrId' => $ndr->getId()]));
        }

        $backLink = $this->generateUrl('ndr_debts_exist', ['ndrId' => $ndrId]);
        if ('summary' == $fromPage) {
            $backLink = $this->generateUrl('ndr_debts_summary', ['ndrId' => $ndrId]);
        }

        return [
            'backLink' => $backLink,
            'ndr' => $ndr,
            'form' => $form->createView(),
        ];
    }

    /**
     * How debts are managed question.
     *
     * @Route("/ndr/{ndrId}/debts/management", name="ndr_debts_management")
     *
     * @Template("@App/Ndr/Debt/management.html.twig")
     */
    public function managementAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Ndr\Debt\DebtManagementType::class, $ndr);

        $form->handleRequest($request);
        $fromPage = $request->get('from');
        $fromSummaryPage = 'summary' == $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('ndr/'.$ndr->getId(), $form->getData(), ['ndr-debt-management']);

            if ('summary' == $fromPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('ndr_debts_summary', ['ndrId' => $ndr->getId()]));
        }

        $backLink = $this->generateUrl('ndr_debts_exist', ['ndrId' => $ndr->getId()]);
        if ('summary' == $fromPage) {
            $backLink = $this->generateUrl('ndr_debts_summary', ['ndrId' => $ndr->getId()]);
        }

        return [
            'backLink' => $backLink,
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('ndr_debts_summary', ['ndrId' => $ndr->getId()]),
            'ndr' => $ndr,
            'form' => $form->createView(),
        ];
    }

    /**
     * List debts.
     *
     * @Route("/ndr/{ndrId}/debts/summary", name="ndr_debts_summary")
     *
     * @Template("@App/Ndr/Debt/summary.html.twig")
     */
    public function summaryAction(Request $request, $ndrId)
    {
        $fromPage = $request->get('from');
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED == $ndr->getStatusService()->getDebtsState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('ndr_debts', ['ndrId' => $ndrId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'ndr' => $ndr,
            'status' => $ndr->getStatusService(),
        ];
    }
}
