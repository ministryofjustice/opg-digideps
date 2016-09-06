<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AbstractController;

class ActionsController extends AbstractController
{
    private static $odrJmsGroups = [
        'odr',
        'client',
        'client-cot',
        'odr-action-give-gifts',
        'odr-action-property',
        'odr-action-more-info',
    ];

    /**
     * @Route("/odr/{odrId}/actions/gifts", name="odr-action-gifts")
     *
     * @param Request $request
     * @param int     $odrId
     * @Template("AppBundle:Odr/Action:gifts.html.twig")
     *
     * @return array
     */
    public function giftsAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

        $form = $this->createForm(new FormDir\Odr\Action\GiftsType(), $odr);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getRestClient()->put('odr/'.$odrId, $form->getData(), ['action-give-gifts']);

            return $this->redirect($this->generateUrl('odr-action-gifts', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
            'subsection' => 'gifts', //property, info
        ];
    }

    /**
     * @Route("/odr/{odrId}/actions/property", name="odr-action-property")
     *
     * @param Request $request
     * @param int     $odrId
     * @Template("AppBundle:Odr/Action:property.html.twig")
     *
     * @return array
     */
    public function propertyAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

        $form = $this->createForm(new FormDir\Odr\Action\PropertyType(), $odr);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getRestClient()->put('odr/'.$odrId, $form->getData(), ['action-property']);

            return $this->redirect($this->generateUrl('odr-action-property', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
            'subsection' => 'property',
        ];
    }

    /**
     * @Route("/odr/{odrId}/actions/info", name="odr-action-info")
     *
     * @param Request $request
     * @param int     $odrId
     * @Template("AppBundle:Odr/Action:info.html.twig")
     *
     * @return array
     */
    public function infoAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

        $form = $this->createForm(new FormDir\Odr\Action\InfoType(), $odr);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getRestClient()->put('odr/'.$odrId, $form->getData(), ['action-more-info']);

            return $this->redirect($this->generateUrl('odr-action-info', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'form' => $form->createView(),
            'subsection' => 'info',
        ];
    }
}
