<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
* @Route("/ad")
*/
class AdController extends AbstractController
{
    /**
     * @Route("/", name="ad_homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return [
        ];
    }
    
    /**
     * @Route("/register", name="ad_register")
     * @Template
     */
    public function registerAction(Request $request)
    {
        // Court order number ?
        // q1, q2, q3
        return [
        ];
    }
    
    /**
     * @Route("/login", name="ad_login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        $form = $this->createForm(new FormDir\AdLoginAsUserType(), null, [
            'action' => $this->generateUrl('ad_login'),
        ]);
        $form->handleRequest($request);
        $vars = [
            'form' => $form->createView(),
        ];
       
        if ($form->isValid()){
            $data = $form->getData();

            try {
                $user = $this->get('deputyprovider')->login($data);
                
                // manually set session token into security context (manual login)
                $token = new UsernamePasswordToken($user,null, "secured_area", $user->getRoles());
                $this->get("security.context")->setToken($token);

                $session = $request->getSession();
                $session->set('_security_secured_area', serialize($token));
                $session->set('loggedOutFrom', null);
                // store AD user
                $session->set('ad', $this->getUser());

                // regenerate cookie, otherwise gc_* timeouts might logout out after successful login
                $session->migrate();
                
                $request->getSession()->getFlashBag()->add(
                    'notice', 
                    'You are now logged as a deputy.'
                );
                
                return $this->redirect($this->generateUrl('client_show'));
                
            } catch(\Exception $e){
                $error = $e->getMessage();

                if ($e->getCode() == 423) {
                    $lockedFor = ceil(($e->getData()['data'] - time()) / 60);
                    $error = $this->get('translator')->trans('bruteForceLocked', ['%minutes%'=>$lockedFor], 'login');
                }
                
                if ($e->getCode() == 499) {
                    // too-many-attempts warning. captcha ?
                }
                
                return $vars + ['error' => $error];
            }
        }
        
        // different page version for timeout and manual logout
        $session = $this->getRequest()->getSession();
        
        return $vars;
    }
    
    /**
     * @Route("/logout", name="ad_logout")
     * @Template
     */
    public function logoutAction(Request $request)
    {
        $session = $request->getSession();
        $adUser = $session->get('ad');
        
        $token = new UsernamePasswordToken($adUser,null, "secured_area", $adUser->getRoles());
        $this->get("security.context")->setToken($token);
        
        $session->set('ad', null);
        $session->migrate();
         
        $session->getFlashBag()->add(
            'notice', 
            'Deputy session terminated.'
        );
        
        $request->setSession($session);
        
        return $this->redirect(
            $this->generateUrl('ad_homepage')
        );
    }
}
