<?php
namespace AppBundle\UserProvider;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use AppBundle\Service\RestClient;
use JMS\Serializer\SerializerInterface;

class DeputyProvider implements UserProviderInterface
{
    /**
     * @var RestClient $restClient
     */
    private $restClient;
    
    /**
     * @var SerializerInterface
     */
    private $jmsSerializer;
    
    public function __construct(RestClient $restClient, SerializerInterface $jmsSerializer)
    {
        $this->restClient = $restClient;
        $this->jmsSerializer = $jmsSerializer;
    }

    
    /**
     * Finds user by email
     * 
     * @param string $email
     * @return \AppBundle\Entity\User $user
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($email) 
    {
        $options = [ 'query' => [ 'email' => $email ] ];
        $response = $this->restClient->get('find_user_by_email', $options);
        
        //if service is down
        if($response->getStatusCode() != 200){
            throw new UsernameNotFoundException("We can't log you in at this time");
        }
        
        $body = $response->getBody();
        $arrayBody = $this->jmsSerializer->deserialize($body,'array','json');
        
        //if request was not successful
        if(!$arrayBody['success']){
           throw new UsernameNotFoundException($arrayBody['message']); 
        }
        
        $user = $this->jmsSerializer->deserialize(json_encode($arrayBody['data']),'AppBundle\Entity\User','json');
        
        return $user;
    }
    
    /**
     * @param UserInterface $user
     * @return  \AppBundle\Entity\User
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }
        return $this->loadUserByUsername($user->getEmail());
    }
    
    /**
     * @param type $class
     * @return type
     */
    public function supportsClass($class)
    {
        return $class === "AppBundle\Entity\User";
    }
}