<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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

    public function __construct(
        ReportApi $reportApi,
        RestClient $restClient
    ) {
        $this->reportApi = $reportApi;
        $this->restClient = $restClient;
    }

    /**
     * @Route("/ndr/{ndrId}/debts", name="ndr_debts")
     * @Template("@App:Ndr/Debt:start.html.twig")
     */
    public function startAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getDebtsState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_debts_summary', ['ndrId' => $ndr->getId()]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/debts/exist", name="ndr_debts_exist")
     * @Template("@App:Ndr/Debt:exist.html.twig")
     */
    public function existAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $ndr,
            [ 'field' => 'hasDebts', 'translation_domain' => 'ndr-debts']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('ndr/' . $ndrId, $ndr, ['debt']);

            if ($ndr->getHasDebts() == 'yes') {
                return $this->redirectToRoute('ndr_debts_edit', ['ndrId' => $ndrId]);
            }

            return $this->redirectToRoute('ndr_debts_summary', ['ndrId' => $ndrId]);
        }

        $backLink = $this->generateUrl('ndr_debts', ['ndrId'=>$ndrId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('ndr_debts_summary', ['ndrId'=>$ndrId]);
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
     * @Template("@App:Ndr/Debt:edit.html.twig")
     */
    public function editAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Ndr\Debt\DebtsType::class, $ndr);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('ndr/' . $ndr->getId(), $form->getData(), ['debt']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Debt edited');
                return $this->redirect($this->generateUrl('ndr_debts_summary', ['ndrId' => $ndrId]));
            }

            return $this->redirect($this->generateUrl('ndr_debts_management', ['ndrId' => $ndr->getId()]));
        }

        $backLink = $this->generateUrl('ndr_debts_exist', ['ndrId'=>$ndrId]);
        if ($fromPage == 'summary') {
            $backLink = $this->generateUrl('ndr_debts_summary', ['ndrId'=>$ndrId]);
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
     * @Template("@App:Ndr/Debt:management.html.twig")
     */
    public function managementAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Ndr\Debt\DebtManagementType::class, $ndr);

        $form->handleRequest($request);
        $fromPage = $request->get('from');
        $fromSummaryPage = $request->get('from') == 'summary';

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('ndr/' . $ndr->getId(), $form->getData(), ['ndr-debt-management']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('ndr_debts_summary', ['ndrId' => $ndr->getId()]));
        }

        $backLink = $this->generateUrl('ndr_debts_exist', ['ndrId' => $ndr->getId()]);
        if ($fromPage == 'summary') {
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
     * @Template("@App:Ndr/Debt:summary.html.twig")
     */
    public function summaryAction(Request $request, $ndrId)
    {
        $fromPage = $request->get('from');
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getDebtsState()['state'] == NdrStatusService::STATE_NOT_STARTED && $fromPage != 'skip-step') {
            return $this->redirectToRoute('ndr_debts', ['ndrId' => $ndrId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'ndr' => $ndr,
            'status' => $ndr->getStatusService()
        ];
    }
}
