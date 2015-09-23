<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
    public function login()
    {
        $data = $this->deserializeBodyContent([
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
        
        // log user in
        $token = new UsernamePasswordToken($user, null, "secured_area", $user->getRoles());
        $this->get("security.context")->setToken($token);
        
        // add random token into response
        $randomToken = $user->getRandomTokenBasedOnInternalData();
        $this->get('kernel.listener.responseConverter')->addResponseModifier(function ($request) use ($randomToken) {
            $request->headers->set('AuthToken', $randomToken);
        });
        
        // store token into the database for following requests
        // TODO
        
        return $user;
    }
    
    /**
     * Return the user by email and hashed password (or exception if not found)
     * 
     * 
     * @Route("/logout")
     * @Method({"POST"})
     */
    public function logout()
    {
        $this->get('security.context')->setToken(null);
        $this->get('request')->getSession()->invalidate();
        
        return true;
    }
    
    /**
     * Test endpoint used for testing to check auth permissions
     * 
     * @Route("/get-logged-user")
     * @Method({"GET"})
     */
    public function test()
    {
        return $this->get('security.context')->getToken()->getUser();
    }
   
}