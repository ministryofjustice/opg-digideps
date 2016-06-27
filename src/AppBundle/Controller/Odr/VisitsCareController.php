<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\ReportStatusService;
use AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class VisitsCareController extends AbstractController
{
    /**
     * @Route("/odr/{odrId}/visits-care", name="odr-visits-care")
     * @Template()
     */
    public function indexAction(Request $request, $odrId)
    {
        $client = $this->getClientOrThrowException();
        $odr = $this->getOdr($client->getId(), ['odr', 'client', 'visits-care']);

        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

        $visitsCare = $odr->getVisitsCare();
        if ($visitsCare === null) {
            $visitsCare = new EntityDir\Odr\VisitsCare();
        }

        $form = $this->createForm(new FormDir\Odr\VisitsCareType(), $visitsCare);

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $data->setOdr($odr);
            $data->keepOnlyRelevantData();

            //TODO simplify endpoint similarly to account, using a PUT on /odr, 'visits-care' subkey
            $deserialiseGroup = [
                'deserialise_groups' => ['visits-care', 'odr-id']
            ];
            if ($visitsCare->getId() === null) {
                $this->getRestClient()->post('/odr/visits-care', $data, $deserialiseGroup);
            } else {
                $this->getRestClient()->put('/odr/visits-care/' . $visitsCare->getId(), $data, $deserialiseGroup);
            }

            //$t = $this->get('translator')->trans('page.safeguardinfoSaved', [], 'report-visitsCare');
            //$this->get('session')->getFlashBag()->add('action', $t);

            return $this->redirect($this->generateUrl('odr-visits-care', ['odrId' => $odrId]) . '#pageBody');
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
        ];
    }
}
