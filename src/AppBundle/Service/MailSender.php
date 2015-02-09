<?php
namespace AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

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
    
    
    protected $fromEmail;
    protected $fromName;
    
    /**
     * @param \AppBundle\Mailer\MailerService $apiClient
     * @param Translator $translator
     */
    public function __construct(ApiClient $apiClient, Translator $translator, UrlGeneratorInterface $router)
    {
        $this->translator = $translator;
        $this->apiClient = $apiClient;
        $this->router = $router;
    }
    
    public function setFrom($fromEmail, $fromName)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

        
    public function sendUserActivationEmail(User $user)
    {
        $params = [
            '%mail%' => $user->getEmail(),
            '%name%' => $user->getFullName(),
            '%link%' => $this->router->generate('user_activate', ['token'=> $user->getRegistrationToken()], true)
        ];
        $subject = $this->translator->trans('activation.subject', $params, 'email');
        $body = $this->translator->trans('activation.body', $params, 'email');
        $bodyHtml = $this->translator->trans('activation.bodyHtml', $params, 'email');
        
        $data = [
            'toEmail' => $user->getEmail(),
            'toName' => $user->getFullName(),
            'fromEmail' => $this->fromEmail,
            'fromName' => $this->fromName,
            'subject' => $subject,
            'bodyText' => $body,
            'bodyHtml' => $bodyHtml
        ];
        
        $ret = $this->apiClient->postC('email/send', json_encode($data));
       
        return $ret;
    }
}
