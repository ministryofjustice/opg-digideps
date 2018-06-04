<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Form as FormDir;
use AppBundle\Service\NdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DebtController extends AbstractController
{
    private static $jmsGroups = ['ndr-debt', 'ndr-debt-management'];

    /**
     * @Route("/ndr/{ndrId}/debts", name="ndr_debts")
     * @Template()
     */
    public function startAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getDebtsState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_debts_summary', ['ndrId' => $ndr->getId()]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/debts/exist", name="ndr_debts_exist")
     * @Template()
     */
    public function existAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $ndr, [ 'field' => 'hasDebts', 'translation_domain' => 'ndr-debts']
                                 );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('ndr/' . $ndrId, $ndr, ['debt']);

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
     * @Template()
     */
    public function editAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Ndr\Debt\DebtsType::class, $ndr);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isValid()) {
            $this->getRestClient()->put('ndr/' . $ndr->getId(), $form->getData(), ['debt']);

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
     * @Template()
     */
    public function managementAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Ndr\Debt\DebtManagementType::class, $ndr);

        $form->handleRequest($request);
        $fromPage = $request->get('from');
        $fromSummaryPage = $request->get('from') == 'summary';

        if ($form->isValid()) {
            $this->getRestClient()->put('ndr/' . $ndr->getId(), $form->getData(), ['ndr-debt-management']);

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
     * @Template()
     */
    public function summaryAction(Request $request, $ndrId)
    {
        $fromPage = $request->get('from');
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
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
