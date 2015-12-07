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
     * @Template("AppBundle:Element:layout.html.twig")
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
}
