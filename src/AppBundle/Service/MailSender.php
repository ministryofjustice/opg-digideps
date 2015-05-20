<?php
namespace AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Validator\Validator;
use AppBundle\Model\Email;

class MailSender
{
     /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @var Translator 
     */
    protected $translator;
    
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router 
     */
    protected $router;
    
    protected $validator;
    
    /**
     * @param \AppBundle\Mailer\MailerService $apiClient
     * @param Translator $translator
     */
    public function __construct(ApiClient $apiClient, Translator $translator, UrlGeneratorInterface $router, Validator $validator)
    {
        $this->translator = $translator;
        $this->apiClient = $apiClient;
        $this->router = $router;
        $this->validator = $validator;
    }

    
    /**
     * @param Email $email
     * @param array $groups
     * @return type
     * @throws \Exception
     */
    public function send(Email $email, array $groups = ['text'])
    {
        //validate change password object
        $errors = $this->validator->validate($email,$groups);
        
        if(count($errors) > 0){
            $errorsString = (string) $errors;
            throw new \Exception($errorsString);
        }
        $ret = $this->apiClient->postC('email/send',$email);
        
        return $ret;
    }
}
