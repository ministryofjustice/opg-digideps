<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
//use AppBundle\Form\LoginType;

class IndexController extends Controller
{
    /**
     * @Route("login", name="login")
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        //$form = $this->createForm(new LoginType());
        //$form->handleRequest($request);
        
        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = null;
        }
        
        // last email entered by the user
        $lastEmail = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        return $this->render(
            'AppBundle:Index:login.html.twig',
            array(
                // last email entered by the user
                'last_email' => $lastEmail,
                'error'         => $error,
                /*'form' => $form*/
            )
        );
    }
    
    /**
     * @Route("login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        return $this->render(
            'AppBundle:Index:login.html.twig',
            array(
                // last email entered by the user
                'last_email' => '',
                'error'         => '',
            )
        );
    }
}