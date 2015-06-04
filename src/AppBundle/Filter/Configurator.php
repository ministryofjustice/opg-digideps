<?php
namespace AppBundle\Filter;

use Symfony\Component\Security\Core\User\UserInterface;   
use Doctrine\Common\Persistence\ObjectManager;  
use Doctrine\Common\Annotations\Reader;

class Configurator  
{
    protected $em;
    protected $reader;
    protected $accessTokenManager;

    public function __construct(ObjectManager $em, Reader $reader, $accessTokenManager)
    {
        $this->em              = $em;
        $this->reader          = $reader;
        $this->accessTokenManager = $accessTokenManager;
    }

    public function onKernelRequest($kernalRequest)
    {
        $request = $kernalRequest->getRequest();
        $session = $request->getSession();
        
        $headers = $request->server->getHeaders();
        $token = str_replace('Bearer',null,$headers['AUTHORIZATION']);
        $tokenTrimmed = str_replace(' ', '', $token);
       
        $user = $this->getUser($tokenTrimmed);
        
        if($user) {
            $session->set('currentUser',$user);
            /*$filter = $this->em->getFilters()->enable('user_filter');
            $filter->setParameter('id', $user->getId());
            $filter->setAnnotationReader($this->reader);
            
            //many to many relations filter
            $filter2 = $this->em->getFilters()->enable('manytomany_relation_filter');
            $filter2->setParameter('id', $user->getId());
            $filter2->setAnnotationReader($this->reader);*/
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