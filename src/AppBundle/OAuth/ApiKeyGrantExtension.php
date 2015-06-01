<?php
namespace AppBundle\OAuth;

use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;

class ApiKeyGrantExtension implements GrantExtensionInterface
{
    private $userRepository;
    
    public function __construct($entityManager)
    {
        $this->userRepository = $entityManager->getRepository('AppBundle:User');
    }
    
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders) 
    {
        $user = $this->userRepository->findOneByPassword($inputData['password_hash']);
        
        if($user){
            return true;
        }
        return false;
    }
}
