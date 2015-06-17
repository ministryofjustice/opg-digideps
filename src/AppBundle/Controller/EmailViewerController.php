<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EmailViewerController extends Controller
{
    /**
     * @Route("/email-viewer/{action}", name="email-viewer")
     * @Template()
     */
    public function emailViewerAction($action)
    {
        if($action == ''){
            die('No action specified');
        }

        $emailToView = 'AppBundle:Email:' . $action .'.html.twig';

        return $this->render($emailToView);
    }

}
