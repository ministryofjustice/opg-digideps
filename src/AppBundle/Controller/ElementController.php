<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/elements")
 */
class ElementController extends AbstractController
{
    /**
     * @Route("", name="elements")
     * @Template("AppBundle:Element:index.html.twig")
     */
    public function indexAction() {return [];}

    /**
     * @Route("/formcomponents", name="elements_form")
     * @Template("AppBundle:Element:forms.html.twig")
     */
    public function formComponentsAction() { return []; }

    /**
     * @Route("/alerts", name="elements_alerts")
     * @Template("AppBundle:Element:alerts.html.twig")
     */
    public function alertsAction() { return []; }

    /**
     * @Route("/navigation", name="elements_navigation")
     * @Template("AppBundle:Element:navigation.html.twig")
     */
    public function navigationAction() { return []; }

    /**
     * @Route("/buttons", name="elements_buttons")
     * @Template("AppBundle:Element:buttons.html.twig")
     */
    public function buttonsAction() { return []; }

    /**
     * @Route("/colour", name="elements_colour")
     * @Template("AppBundle:Element:colour.html.twig")
     */
    public function colourAction() { return []; }

    /**
     * @Route("/components", name="elements_components")
     * @Template("AppBundle:Element:components.html.twig")
     */
    public function componentsAction(){ return []; }

        /**
     * @Route("/icons", name="elements_icons")
     * @Template("AppBundle:Element:icons.html.twig")
     */
    public function iconsAction(){ return []; }

}
