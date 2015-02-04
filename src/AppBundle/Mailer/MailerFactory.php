<?php
namespace AppBundle\Mailer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class MailerFactory
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $router;
    
     /**
     * @var MailerService
     */
    protected $mailerService;

    protected $translator;
    
    protected $fromEmail;
    protected $fromName;
    
    /**
     * @param \AppBundle\Mailer\MailerService $mailerService
     * @param Translator $translator
     */
    public function __construct(MailerService $mailerService, Translator $translator)
    {
        $this->translator = $translator;
        $this->mailerService = $mailerService;
    }
    
    public function setFrom($fromEmail, $fromName)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

        
    public function sendActivationEmail(User $user)
    {
        $message = $this->mailerService->createMessage();
        $message->setTo($user->getEmail(), $user->getFullName());
        $message->setFrom($this->fromEmail, $this->fromName);
        

        $params = [
            '%mail%' => $user->getEmail(),
            '%name%' => $user->getFullName(),
            '%link%' => 'http://link.com/activate/' . $user->getRegistrationToken()
        ];
        $subject = $this->translator->trans('activation.subject', $params, 'email');
        $body = $this->translator->trans('activation.body', $params, 'email');
        $bodyHtml = $this->translator->trans('activation.bodyHtml', $params, 'email');
        
        $message->setSubject($subject);
        
        $this->mailerService->sendMimeMessage($message, $body, $bodyHtml);
    }
}
