<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use AppBundle\Service\Auth\HeaderTokenAuthenticator;
use AppBundle\Service\Auth\UserProviders\UserByTokenProviderInterface;

/**
 * @Route("/auth")
 */
class AuthController extends RestController
{
    const HEADER_CLIENT_SECRET = 'ClientSecret';
    
    /**
     * Return the user by email and hashed password (or exception if not found)
     * 
     * 
     * @Route("/login")
     * @Method({"POST"})
     */
    public function login(Request $request)
    {
        // check client secret
        //TODO consider moving into provider or authmanager ?
        $clientSecrets = $this->container->getParameter('client_secrets');
        $clientSecretGiven = $request->headers->get(self::HEADER_CLIENT_SECRET);
        $permissions = isset($clientSecrets[$clientSecretGiven]) ?
            $clientSecrets[$clientSecretGiven]['permissions'] : null;
        if (null === $permissions) {
            throw new \RuntimeException('client secret not accepted.');
        }
        
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
        
        $userRole = $user->getRole()->getRole();
        if (!in_array($userRole, $permissions)) {
            throw new \RuntimeException(sprintf('Given client secret only allows roles %s, %s given.',
                implode(',', $permissions),
                $userRole
            ));
        }
        
        $randomToken = $this->getProvider()->generateAndStoreToken($user);
        
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
    public function test()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
   
}