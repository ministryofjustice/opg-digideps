<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class VisitsCareController extends AbstractController
{
    private static $odrJmsGroups = [
        'odr',
        'client',
        'visits-care',
    ];

    /**
     * @Route("/odr/{odrId}/visits-care", name="odr-visits-care")
     * @Template()
     */
    public function indexAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
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

            if ($visitsCare->getId() === null) {
                $this->getRestClient()->post('/odr/visits-care', $data, ['visits-care', 'odr-id']);
            } else {
                $this->getRestClient()->put('/odr/visits-care/'.$visitsCare->getId(), $data, ['visits-care', 'odr-id']);
            }

            return $this->redirect($this->generateUrl('odr-visits-care', ['odrId' => $odrId]).'#pageBody');
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
        ];
    }
}
