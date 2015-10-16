<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\Auth\HeaderTokenAuthenticator;
use AppBundle\Service\Auth\UserProvider;
use AppBundle\Service\Auth\AuthService;
use AppBundle\Exception as AppException;

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
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new AppException\UnauthorisedException('client secret not accepted.');
        }
        $data = $this->deserializeBodyContent($request);
        
        $bruteForceChecker = $this->get('bruteForceChecker');
        
        // load user by credentials (token or usernae&password)
        if (array_key_exists('token', $data)) {
            $user = $this->getAuthService()->getUserByToken($data['token']);
        } else {
            $email = strtolower($data['email']);
            $password = $data['password'];
            if (!$bruteForceChecker->isAllowed($email, $password)) {
                throw new AppException\BruteForceDetectedException("Too many attemptes");
            }
            $user = $this->getAuthService()->getUserByEmailAndPassword($email, $password);
        }
        if (!$user) {
            // incase the user is not found or the password is not valid (same error given for security reasons)
            throw new AppException\UserWrongCredentials();
        }
        if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new AppException\UnauthorisedException($user->getRole()->getRole() . ' user role not allowed from this client.');
        }
        
        if (isset($email)) {
            $bruteForceChecker->resetAttacksByEmail($email);
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