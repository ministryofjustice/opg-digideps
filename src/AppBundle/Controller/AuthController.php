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
        
        $key = array_key_exists('token', $data) ? 'token' : 'email';
        $this->bruteForceRegisterAttemptAndCheckIfAllowed($key, $data);
        
        // load user by credentials (token or username & password)
        if (array_key_exists('token', $data)) {
            $user = $this->getAuthService()->getUserByToken($data['token']);
        } else {
            $user = $this->getAuthService()->getUserByEmailAndPassword(strtolower($data['email']),  $data['password']);
        }
        
        if (!$user) {
            // incase the user is not found or the password is not valid (same error given for security reasons)
            throw new AppException\UserWrongCredentials();
        }
        if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new AppException\UnauthorisedException($user->getRole()->getRole() . ' user role not allowed from this client.');
        }
        
        $this->get('attemptsChecker.returnCode')->resetAttempts($key);
        $this->get('attemptsChecker.exception')->resetAttempts($key);
        
        $randomToken = $this->getProvider()->generateRandomTokenAndStore($user);
        $user->setLastLoggedIn(new \DateTime);
        $this->get('em')->flush($user);
        
        // add token into response
        $this->get('kernel.listener.responseConverter')->addResponseModifier(function ($response) use ($randomToken) {
            $response->headers->set(HeaderTokenAuthenticator::HEADER_NAME, $randomToken);
        });
        
        return $user;
    }
    
    private function bruteForceRegisterAttemptAndCheckIfAllowed($index, $data)
    {
        $returnCodeChecker = $this->get('attemptsChecker.returnCode');
        $exceptionChecker = $this->get('attemptsChecker.exception');
        
        $key = $index . $data[$index];
        $returnCodeChecker->registerAttempt($key); //e.g emailName@example.org
        $exceptionChecker->registerAttempt($key);
        
        // exception if reached delay-check
        if ($exceptionChecker->maxAttemptsReached()) {
            $nextAttemptIn = ceil($exceptionChecker->secondsBeforeNextAttempt() / 60);
            throw new AppException\UnauthorisedException(423, "Attack detected. Please try again in $nextAttemptIn minutes");
        }
        
        // set return code to 202
        if ($returnCodeChecker->maxAttemptsReached($key)) {
             $this->get('kernel.listener.responseConverter')->addResponseModifier(function ($response){
                $response->setStatusCode(202);
            });
        }
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