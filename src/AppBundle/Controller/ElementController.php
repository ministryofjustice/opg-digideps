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
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/base", name="elements")
     * @Template("AppBundle:Element:base.html.twig")
     */
    public function baseAction()
    {
        $report = new Report();
        $report->setId(1);
        $report->setCourtOrderTypeId(2);

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/layout", name="elements_layout")
     * @Template("AppBundle:Element/layout:layout.html.twig")
     */
    public function layoutAction()
    {
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Layout'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
        ];
    }

    /**
     * @Route("/colour", name="elements_colour")
     * @Template("AppBundle:Element:colour.html.twig")
     */
    public function colourAction()
    {
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Colours'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
        ];
    }

    /**
     * @Route("/formcomponents", name="elements_form")
     * @Template("AppBundle:Element/form:form.html.twig")
     */
    public function formComponentsAction()
    {
        $client = [
            'fullname' => 'Zac Tolley',
        ];

        $report = [
            'id' => 1,
            'period' => '2014 to 2015',
            'client' => $client,
        ];

        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Form elements'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
            'report' => $report,
            'client' => $client,
        ];
    }

    /**
     * @Route("/hero", name="elements_hero")
     * @Template("AppBundle:Element:hero.html.twig")
     */
    public function heroAction()
    {
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Hero elements'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
        ];
    }

    /**
     * @Route("/headings", name="elements_headings")
     * @Template("AppBundle:Element:headings.html.twig")
     */
    public function headingsAction()
    {
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Headings'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
        ];
    }

    /**
     * @Route("/navigation", name="elements_navigation")
     * @Template("AppBundle:Element:navigation.html.twig")
     */
    public function navigationAction()
    {
        $client = [
            'fullname' => 'Zac Tolley',
        ];

        $report = [
            'id' => 1,
            'period' => '2014 to 2015',
            'client' => $client,
        ];

        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Navigation'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
            'report' => $report,
            'client' => $client,
        ];
    }
}
