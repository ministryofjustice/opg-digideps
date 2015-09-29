<?php

namespace AppBundle\Service\Auth;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;
use Symfony\Bridge\Monolog\Logger;

class AuthService
{
    const HEADER_CLIENT_SECRET = 'ClientSecret';
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->clientSecrets = $container->getParameter('client_secrets');
        if (!is_array($this->clientSecrets) || empty($this->clientSecrets)) {
            throw new \InvalidArgumentException('client_secrets not defined in config.');
        }
        $this->container = $container;
        $this->userRepo = $container->get('em')->getRepository('AppBundle\Entity\User');
        $this->logger = $container->get('logger');
        $this->securityEncoderFactory = $container->get('security.encoder_factory');
    }
    
    /**
     * @return array
     */
    public function isSecretValid($clientSecretFromRequest)
    {
        return isset($this->clientSecrets[$clientSecretFromRequest]);
    }
    
    /**
     * @param Request $request
     * 
     * @return string
     */
    public function getClientSecretFromRequest(Request $request)
    {
        return $request->headers->get(self::HEADER_CLIENT_SECRET);
    }
    
    /**
     * @param string $email
     * @param string $pass
     * 
     * @return User|null
     */
    public function getUserByEmailAndPassword($email, $pass)
    {
        $this->logger->info(__METHOD__." called with $email and $pass ");
        
        if (!$email || !$pass) {
            return null;
        }
         // get user by email
        $user = $this->userRepo->findOneBy([
            'email'=> $email
        ]);
        if (!$user instanceof User) {
            $this->logger->info(__METHOD__." user not found ");
            return false;
        }
        
        // check hashed password matching
        $encodedPass = $this->securityEncoderFactory->getEncoder($user)
            ->encodePassword($pass, $user->getSalt());
        
        if ($user->getPassword() == $encodedPass) {
            $this->logger->info(__METHOD__." password match ");
            return $user;
        } 
        
        $this->logger->info(__METHOD__." password mismatch ");
        
        
        return null;
    }
    
    /**
     * @param User $user
     * @param string $clientSecretFromRequest
     * 
     * @return boolean
     */
    public function isSecretValidForUser(User $user, $clientSecretFromRequest)
    {
        $this->logger->info(__METHOD__." with user Id {$user->getId()} and $clientSecretFromRequest ");
        
        $permissions = $this->clientSecrets[$clientSecretFromRequest]['permissions'];
        
        $userRole = $user->getRole()->getRole();
        
        $valid = in_array($userRole, $permissions);
        
        $this->logger->info(__METHOD__ . ' ' . ($valid ? 'valid' : 'NOT valid'));
        
        return $valid;
    }
}