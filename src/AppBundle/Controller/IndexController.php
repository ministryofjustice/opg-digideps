<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\LoginType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormError;

class IndexController extends Controller
{
    /**
     * @Route("login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        $request = $this->getRequest();

        $form = $this->createForm(new LoginType());
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $deputyProvider = $this->get('deputyprovider');
                $data = $form->getData();
                
                try{
                    $user = $deputyProvider->loadUserByUsername($data['email']);
                    
                    $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                    
                    if(!$encoder->isPasswordValid($user->getPassword(), $data['password'], $user->getSalt())){
                        throw new \Exception("Invalid email or password");
                    }
                    
                }catch(\Exception $e){
                    return [ 'form' => $form->createView(), 'error' => $e ];
                }
                
                $token = new UsernamePasswordToken($user,null, "secured_area", $user->getRoles());
                $this->get("security.context")->setToken($token);
                
                $this->get('session')->set('_security_secured_area', serialize($token));
                
                $request = $this->get("request");
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            }
        }
        return [ 'form' => $form->createView()];
    }
    
    /**
     * @Route("login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        die('2');
    }
    
    /**
     * @Route("/access-denied", name="access_denied")
     */
    public function accessDeniedAction()
    {
        throw new AccessDeniedException();
    }
}