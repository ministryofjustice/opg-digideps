<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\ReportType;

/**
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/create", name="report_create")
     * @Template()
     */
    public function createAction()
    {
        $request = $this->getRequest();
        
        $form = $this->createForm(new ReportType($this->get('util')));
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                die('sfsdfds');
            }
        }
        
        return [ 'form' => $form->createView() ];
    }
}