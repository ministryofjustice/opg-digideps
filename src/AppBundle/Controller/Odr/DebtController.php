<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DebtController extends AbstractController
{
    private static $jmsGroups = ['odr-debt', 'odr-debt-management'];

    /**
     * @Route("/odr/{odrId}/debts", name="odr_debts")
     * @Template()
     */
    public function startAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        if ($odr->getStatusService()->getDebtsState()['state'] != OdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('odr_debts_summary', ['odrId' => $odr->getId()]);
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/debts/exist", name="odr_debts_exist")
     * @Template()
     */
    public function existAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $odr, [ 'field' => 'hasDebts', 'translation_domain' => 'odr-debts']
                                 );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('odr/' . $odrId, $odr, ['debt']);

            if ($odr->getHasDebts() == 'yes') {
                return $this->redirectToRoute('odr_debts_edit', ['odrId' => $odrId]);
            }

            $request->getSession()->getFlashBag()->add('notice', 'Debt edited');
            return $this->redirectToRoute('odr_debts_summary', ['odrId' => $odrId]);
        }

        $backLink = $this->generateUrl('odr_debts', ['odrId'=>$odrId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('odr_debts_summary', ['odrId'=>$odrId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'odr' => $odr,
        ];
    }

    /**
     * List debts.
     *
     * @Route("/odr/{odrId}/debts/edit", name="odr_debts_edit")
     * @Template()
     */
    public function editAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Odr\Debt\DebtsType::class, $odr);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isValid()) {
            $this->getRestClient()->put('odr/' . $odr->getId(), $form->getData(), ['debt']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Debt edited');
                return $this->redirect($this->generateUrl('odr_debts_summary', ['odrId' => $odrId]));
            }

            return $this->redirect($this->generateUrl('odr_debts_management', ['odrId' => $odr->getId()]));
        }

        $backLink = $this->generateUrl('odr_debts_exist', ['odrId'=>$odrId]);
        if ($fromPage == 'summary') {
            $backLink = $this->generateUrl('odr_debts_summary', ['odrId'=>$odrId]);
        }

        return [
            'backLink' => $backLink,
            'odr' => $odr,
            'form' => $form->createView(),
        ];
    }

    /**
     * How debts are managed question.
     *
     * @Route("/odr/{odrId}/debts/management", name="odr_debts_management")
     * @Template()
     */
    public function managementAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Odr\Debt\DebtManagementType::class, $odr);

        $form->handleRequest($request);
        $fromPage = $request->get('from');
        $fromSummaryPage = $request->get('from') == 'summary';

        if ($form->isValid()) {
            $this->getRestClient()->put('odr/' . $odr->getId(), $form->getData(), ['odr-debt-management']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('odr_debts_summary', ['odrId' => $odr->getId()]));
        }

        $backLink = $this->generateUrl('odr_debts_exist', ['odrId' => $odr->getId()]);
        if ($fromPage == 'summary') {
            $backLink = $this->generateUrl('odr_debts_summary', ['odrId' => $odr->getId()]);
        }

        return [
            'backLink' => $backLink,
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('odr_debts_summary', ['odrId' => $odr->getId()]),
            'odr' => $odr,
            'form' => $form->createView(),
        ];
    }

    /**
     * List debts.
     *
     * @Route("/odr/{odrId}/debts/summary", name="odr_debts_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        if ($odr->getStatusService()->getDebtsState()['state'] == OdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('odr_debts', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }
}
