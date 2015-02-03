<?php
namespace AppBundle\Mailer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\User;

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

    
    public function __construct(MailerService $mailerService, UrlGeneratorInterface $router)
    {
        $this->router = $router;
        $this->mailerService = $mailerService;
    }
    
    public function sendActivationEmail(User $user)
    {
//        $link = $this->router->generate('verification_verify', [
//            'token' => $user->generateToken()->getToken()
//        ], true);

        $message = $this->mailerService->createMessage();
        $message->setTo($user->getEmail());

//        $this->view->setTemplate('view/user/registration/account-verify.txt.twig');
//        $this->view->setHtmlTemplate('view/user/registration/account-verify.html.twig');

        $this->mailerService->sendMimeMessage($message, 'subject', 'body');
    }
}
