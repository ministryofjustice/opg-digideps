<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DebtController extends AbstractController
{
    /**
     * List debts.
     *
     * @Route("/odr/{odrId}/debts", name="odr-debts")
     * @Template("AppBundle:Odr/Debt:list.html.twig")
     */
    public function listAction(Request $request, $odrId)
    {
        $odr = $this->getReport($odrId, ['debts', 'basic', 'client'/*, 'transactions', 'asset', 'accounts'*/]);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }

        $form = $this->createForm(new FormDir\Odr\DebtsType(), $odr);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('restClient')->put('odr/'.$odr->getId(), $form->getData(), [
                'deserialise_group' => 'debts',
            ]);

            return $this->redirect($this->generateUrl('debts', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
        ];
    }
}
