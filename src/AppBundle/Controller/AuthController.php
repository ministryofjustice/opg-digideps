<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Service\HeaderTokenAuthenticator;

/**
 * @Route("/auth")
 */
class AuthController extends RestController
{
    /**
     * Return the user by email and hashed password (or exception if not found)
     * 
     * 
     * @Route("/login")
     * @Method({"POST"})
     */
    public function login(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
            'email' => 'notEmpty',
            'password' => 'notEmpty',
        ]);
        
        // get user by email
        $user = $this->findEntityBy('User', [
            'email'=> $data['email']
        ], 'User not found');
        
        // check hashed password matching
        $encodedPass = $this->get('security.encoder_factory')
            ->getEncoder($user)
            ->encodePassword($data['password'], $user->getSalt());
        if (!$user->getPassword() || $user->getPassword() != $encodedPass) {
            throw new \RuntimeException('Cannot find user with the given username and password');
        }
        
        $randomToken = $this->get('get_user_by_token_provider')->generateAndStoreToken($user);
        
        // add token into response
        $this->get('kernel.listener.responseConverter')->addResponseModifier(function ($request) use ($randomToken) {
            $request->headers->set(HeaderTokenAuthenticator::HEADER_NAME, $randomToken);
        });
        
        return $user;
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
       
       return $this->get('get_user_by_token_provider')->removeToken($authToken);
    }
    
    /**
     * Test endpoint used for testing to check auth permissions
     * 
     * @Route("/get-logged-user")
     * @Method({"GET"})
     */
    public function test()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
   
}