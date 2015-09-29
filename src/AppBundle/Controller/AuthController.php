<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use AppBundle\Service\Auth\HeaderTokenAuthenticator;
use AppBundle\Service\Auth\UserProviders\UserByTokenProviderInterface;
use AppBundle\Service\Auth\AuthService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @Route("/auth")
 */
class AuthController extends RestController
{
    /**
     * Return the user by email and hashed password (or exception if not found)
     * 
     * @Route("/login")
     * @Method({"POST"})
     */
    public function login(Request $request)
    {
        $authService = $this->get('authService'); /* @var $authService AuthService */
        
        $clientSecretFromRequest = $authService->getClientSecretFromRequest($request);
        if (!$authService->isSecretValid($clientSecretFromRequest)) {
            throw new \RuntimeException('client secret not accepted.');
        }
        
        $data = $this->deserializeBodyContent($request, [
            'email' => 'notEmpty',
            'password' => 'notEmpty',
        ]);
        $user = $authService->getUserByEmailAndPassword($data['email'], $data['password']);
        if (!$user) {
            throw new \RuntimeException('Cannot find user with the given username and password');
        }
        if (!$authService->isSecretValidForUser($user, $clientSecretFromRequest)) {
            throw new \RuntimeException($user->getRole()->getRole() . ' user role not allowed from this client.');
        }
        
        $randomToken = $this->getProvider()->generateAndStoreToken($user);
        $user->setLastLoggedIn(new \DateTime);
        $this->get('em')->flush($user);
        
        // add token into response
        $this->get('kernel.listener.responseConverter')->addResponseModifier(function ($request) use ($randomToken) {
            $request->headers->set(HeaderTokenAuthenticator::HEADER_NAME, $randomToken);
        });
        
        return $user;
    }
    
    /**
     * @return UserByTokenProviderInterface
     */
    private function getProvider()
    {
        $service = $this->container->getParameter('get_user_by_token_provider.class');
        
        return $this->get($service);
    }
    
    /**
     * Return the user by email and hashed password (or exception if not found)
     * 
     * 
     * @Route("/logout")
     * @Method({"POST"})
     */
    public function logout(Request $request)
    {
        $authToken = HeaderTokenAuthenticator::getTokenFromRequest($request);
       
        return $this->getProvider()->removeToken($authToken);
    }
    
    /**
     * Test endpoint used for testing to check auth permissions
     * 
     * @Route("/get-logged-user")
     * @Method({"GET"})
     */
    public function getLoggedUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
   
}