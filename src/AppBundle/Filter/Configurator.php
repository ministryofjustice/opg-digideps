<?php
namespace AppBundle\Filter;

use Symfony\Component\Security\Core\User\UserInterface;   
use Doctrine\Common\Persistence\ObjectManager;  

class Configurator  
{
    protected $em;
   
    protected $accessTokenManager;

    public function __construct(ObjectManager $em, $accessTokenManager)
    {
        $this->em              = $em;
        $this->accessTokenManager = $accessTokenManager;
    }

    public function onKernelRequest($kernalRequest)
    {
        $request = $kernalRequest->getRequest();
        $session = $request->getSession();
        
        $headers = $request->server->getHeaders();
        
        if(isset($headers['AUTHORIZATION'])){
            $token = str_replace('Bearer',null,$headers['AUTHORIZATION']);
            $tokenTrimmed = str_replace(' ', '', $token);

            $user = $this->getUser($tokenTrimmed);

            if($user) {
                $session->set('currentUser',$user);
            }
        }
    }

    private function getUser($token)
    {
        $accessTokenManager = $this->accessTokenManager->findTokenBy(['token' => $token ]);
        
        if(!$accessTokenManager){
            return null;
        }
        
        $user = $accessTokenManager->getUser();
       
        if (!($user instanceof UserInterface)) {
            return null;
        }
        return $user;
    }
}