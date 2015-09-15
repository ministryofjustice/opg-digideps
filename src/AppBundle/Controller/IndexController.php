<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use AppBundle\Service\ApiClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return new RedirectResponse($this->get('redirectorService')->getUserFirstPage(false));
    }
    
    /**
     * @Route("login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $oauth2Enabled = $this->container->getParameter('oauth2_enabled');
        $useMemcached = $this->container->getParameter('use_memcached');
        $useRedis = $this->container->getParameter('use_redis');

        $form = $this->createForm(new FormDir\LoginType(), null, [
            'action' => $this->generateUrl('login'),
        ]);
        $form->handleRequest($request);
        $vars = [
            'form' => $form->createView(),
        ];
       
        if ($form->isValid()){
            $deputyProvider = $this->get('deputyprovider');
            $data = $form->getData();
            
            if($oauth2Enabled){
                if($useRedis){
                    $storage = $this->get('snc_redis.default');
                }elseif($useMemcached){
                    $storage = $this->get('oauth.memcached');
                }else{
                    $oauth2Enabled = false;
                }
            }
            
            try{
                $user = $deputyProvider->loadUserByUsername($data['email']);
                
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);

                // exception if credentials not valid
                if(!$encoder->isPasswordValid($user->getPassword(), $data['password'], $user->getSalt())){
                    $message = $this->get('translator')->trans('login.invalidMessage', [], 'login');
                    throw new \Exception($message);
                }
            } catch(\Exception $e){
                return $this->render('AppBundle:Index:login.html.twig', $vars + ['error' => $e->getMessage()]);
            }
            // manually set session token into security context (manual login)
            $token = new UsernamePasswordToken($user,null, "secured_area", $user->getRoles());
            $this->get("security.context")->setToken($token);
            
            $session = $request->getSession();
            $session->set('_security_secured_area', serialize($token));
            $session->set('loggedOutFrom', null);
            
            // regenerate cookie, otherwise gc_* timeouts might logout out after successful login
            $session->migrate();
            
            if($oauth2Enabled){
                if($useRedis){
                    $storage->set($session->getId().'_user_credentials', serialize([ 'email' => $user->getEmail(), 'password' => $user->getPassword() ]));
                    //$storage->hset($session->getId().'_user_credentials','email', $user->getEmail());
                    //$storage->hset($session->getId().'_user_credentials','password', $user->getPassword());
                    $storage->expire($session->getId().'_user_credentials',3600);
                    
                }elseif($useMemcached){
                    $credentials = $storage->get($session->getId().'_user_credentials');
                    
                    if(!$credentials){
                        $storage->add($session->getId().'_user_credentials',[ 'email' => $user->getEmail(),'password' => $user->getPassword()],3600);
                    }else{
                        $storage->replace($session->getId().'_user_credentials',[ 'email' => $user->getEmail(), 'password' => $user->getPassword()],3600);
                    }
                }
            }
            
            $request = $this->get("request");
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            
            $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */

            $session->set('lastLoggedIn', $user->getLastLoggedIn());
            $user->setLastLoggedIn(new \DateTime()); //save for future access
            $apiClient->putC('user/' .  $user->getId(), $user, [
                'deserialise_group' => 'lastLoggedIn',
            ]);
            
            $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_LOGIN);
        }
        
        // different page version for timeout and manual logout
        $session = $this->getRequest()->getSession();
        
        if ($session->get('loggedOutFrom') === 'logoutPage') {
            $session->set('loggedOutFrom', null); //avoid display the message at next page reload
            return $this->render('AppBundle:Index:login-from-logout.html.twig', $vars);
        } else if ($session->get('loggedOutFrom') === 'timeout') {
            $session->set('loggedOutFrom', null); //avoid display the message at next page reload
            $vars['error'] = $this->get('translator')->trans('sessionTimeoutOutWarning', [], 'login');
        }
            
        return $this->render('AppBundle:Index:login.html.twig', $vars);
    }


    /**
     * @Route("login_check", name="login_check")
     */
    public function loginCheckAction()
    {
    }

    /**
     * @Route("error-503", name="error-503")
     */
    public function error503()
    {
        $request = $this->getRequest();
        $vars = [];
        $vars['request'] = $request;

        return $this->render('AppBundle:Index:error-503.html.twig', $vars);
    }
    
    /**
     * keep session alive. Called from session timeout dialog
     * 
     * @Route("session-keep-alive", name="session-keep-alive")
     * @Method({"POST"})
     */
    public function sessionKeepAliveAction(Request $request)
    {
        $request->getSession()->set('refreshedAt', time());
        
        return new Response('session refreshed successfully');
    }

    /**
     * @Route("/access-denied", name="access_denied")
     */
    public function accessDeniedAction()
    {
        throw new AccessDeniedException();
    }
    
    
    private function initProgressIndicator($array, $currentStep)
    {
        $currentStep = $currentStep - 1;
        $progressSteps_arr = array();
        if (is_array($array)) {
            $soa = count($array);

            for ($i = 0; $i < $soa; $i++) {
                $item = $array[$i];
                $classes = [];
                if ($i == $currentStep) {
                    $classes[] = 'progress--active';
                }
                if ($i < $currentStep) {
                    $classes[] = 'progress--completed';
                }
                if ($i == ($currentStep - 1)) {
                    $classes[] = 'progress--previous';
                }
                $item['class'] = implode(' ', $classes);

                $progressSteps_arr[] = $item;
            }

        }

        return $progressSteps_arr;
    }

    /**
     * @Route("/feedback", name="feedback")
     */
    public function feedbackAction()
    {
        $form = $this->createForm('feedback', new ModelDir\Feedback());
        $request = $this->getRequest();
        
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            
            if($form->isValid()){
                
                $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
                
                $apiClient->postC('/feedback', $form->getData());
                
                return $this->render('AppBundle:Index:feedback-thankyou.html.twig');
            }
        }
        return $this->render('AppBundle:Index:feedback.html.twig', [ 'form' => $form->createView() ]);
    }
    
    /**
     * @Route("/terms", name="terms")
     */
    public function termsAction()
    {
        return $this->render('AppBundle:Index:terms.html.twig');
    }

}
