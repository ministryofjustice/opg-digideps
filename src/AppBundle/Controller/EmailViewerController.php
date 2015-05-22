<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Form\LoginType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormError;
use AppBundle\EventListener\SessionListener;
use AppBundle\Service\ApiClient;

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
