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

    /**
     */
    protected $translator;
    
    
    public function __construct(MailerService $mailerService, Translator $translator)
    {
        $this->translator = $translator;
        $this->mailerService = $mailerService;
    }
    
    public function sendActivationEmail(User $user)
    {
        $message = $this->mailerService->createMessage();
        $message->setTo($user->getEmail());

        $params = [
            '%mail%' => $user->getEmail(),
            '%name%' => $user->getFirstname() . ' ' . $user->getLastname(),
            '%link%' => 'http://link.com/activate/' . $user->getRegistrationToken()
        ];
        $subject = $this->translator->trans('activation.subject', $params, 'email');
        $body = $this->translator->trans('activation.body', $params, 'email');
        
        $this->mailerService->sendMimeMessage($message, $subject, $body);
    }
}
