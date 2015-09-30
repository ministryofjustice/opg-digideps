<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use AppBundle\Service\Auth\HeaderTokenAuthenticator;
use AppBundle\Service\Auth\UserProvider;
use AppBundle\Service\Auth\AuthService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @Route("/auth")
 */
class AuthController extends RestController
{
    /**
     * Return the user by email&password or token
     * expected keys in body: 'token' or ('email' and 'password')
     * 
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
        
        $data = $this->deserializeBodyContent($request);
        
        // load user by credentials (token or usernae&password)
        if (array_key_exists('token', $data)) {
            $user = $authService->getUserByToken($data['token']);
        } else {
            $user = $authService->getUserByEmailAndPassword(strtolower($data['email']), $data['password']);
        }
        if (!$user) {
            throw new \RuntimeException('Cannot find user with the given credentials');
        }
        if (!$authService->isSecretValidForUser($user, $clientSecretFromRequest)) {
            throw new \RuntimeException($user->getRole()->getRole() . ' user role not allowed from this client.');
        }
        
        $randomToken = $this->getProvider()->generateRandomTokenAndStore($user);
        $user->setLastLoggedIn(new \DateTime);
        $this->get('em')->flush($user);
        
        // add token into response
        $this->get('kernel.listener.responseConverter')->addResponseModifier(function ($request) use ($randomToken) {
            $request->headers->set(HeaderTokenAuthenticator::HEADER_NAME, $randomToken);
        });
        
        return $user;
    }
    
    /**
     * @return UserProvider
     */
    private function getProvider()
    {
        return $this->container->get('user_provider');
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