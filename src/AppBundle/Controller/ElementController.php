<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/layout", name="elements_layout")
     * @Template("AppBundle:Element/layout:layout.html.twig")
     */
    public function layoutAction()
    {
        
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Layout']
            
        ];
        
        return [
            'breadCrumb' => $breadCrumb
        ];
    
    }

    /**
     * @Route("/twigcomponents", name="elements_twig")
     * @Template("AppBundle:Element/Twig:twig.html.twig")
     */
    public function twigComponentsAction()
    {
        
        $client = [
            'fullname' => 'Zac Tolley'
        ];
        
        $report = [
            'id' => 1,
            'period' => '2014 to 2015',
            'client' => $client
        ];
        
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Twig components']

        ];

        return [
            'breadCrumb' => $breadCrumb,
            'report' => $report,
            'client' => $client
        ];

    }

    /**
     * @Route("/formcomponents", name="elements_form")
     * @Template("AppBundle:Element/form:form.html.twig")
     */
    public function formComponentsAction()
    {

        $client = [
            'fullname' => 'Zac Tolley'
        ];

        $report = [
            'id' => 1,
            'period' => '2014 to 2015',
            'client' => $client
        ];

        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Form elements']

        ];

        return [
            'breadCrumb' => $breadCrumb,
            'report' => $report,
            'client' => $client
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
            ['label' => 'Hero elements']

        ];

        return [
            'breadCrumb' => $breadCrumb
        ];

    }
}
