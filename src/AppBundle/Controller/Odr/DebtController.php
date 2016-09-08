<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class DebtController extends AbstractController
{
    private static $odrJmsGroups = ['odr', 'client', 'odr-debt'];

    /**
     * List debts.
     *
     * @Route("/odr/{odrId}/debts", name="odr-debts")
     * @Template("AppBundle:Odr/Debt:list.html.twig")
     */
    public function listAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

        $form = $this->createForm(new FormDir\Odr\DebtsType(), $odr);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $odr->getHasDebts() =='yes' && !$odr->hasAtLeastOneDebt()) {
            $form->addError(new FormError($this->get('translator')->trans('odr.debt.atLeastOne', [], 'validators')));
        }

        if ($form->isValid()) {
            $this->get('restClient')->put('odr/'.$odr->getId(), $form->getData(), ['debts']);

            return $this->redirect($this->generateUrl('odr-debts', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
        ];
    }
}
