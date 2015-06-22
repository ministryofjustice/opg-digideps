<?php
namespace AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Validator\Validator;
use AppBundle\Model\Email;
use Symfony\Component\DependencyInjection\Container;
use AppBundle\Entity as EntityDir;


class MailFactory
{
    /**
     * @var Translator 
     */
    protected $translator;
    
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router 
     */
    protected $router;
    
     /**
     * @var Container
     */
    protected $container;

    
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->translator = $container->get('translator');
        $this->router = $container->get('router');
        $this->validator = $container->get('validator');
        $this->templateing = $container->get('templating');
    }
    
    public function createActivationEmail(EntityDir\User $user)
    {
        // send activation link
        $emailConfig = $this->container->getParameter('email_send');

        $email = new Email();
        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $this->router->generate('homepage', [], true),
            'link' => $this->router->generate('user_activate', ['token'=> $user->getRegistrationToken()], true)
        ];
        $email->setFromEmail($emailConfig['from_email'])
            ->setFromName($this->translator->trans('activation.fromName',[], 'email'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFullName())
            ->setSubject($this->translator->trans('activation.subject',[], 'email'))
            ->setBodyHtml($this->templateing->render('AppBundle:Email:user-activate.html.twig', $viewParams))
            ->setBodyText($this->templateing->render('AppBundle:Email:user-activate.text.twig', $viewParams));

        return $email;
    }
    
    public function createResetPasswordEmail(EntityDir\User $user)
    {
        // send activation link
        $emailConfig = $this->container->getParameter('email_send');

        $email = new Email();
        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $this->router->generate('homepage', [], true),
            'link' => $this->router->generate('user_activate', [
                'action'=>'password-reset', 
                'token'=> $user->getRegistrationToken()
                ], true)
        ];
        
        $email->setFromEmail($emailConfig['from_email'])
            ->setFromName($this->translator->trans('resetPassword.fromName',[], 'email'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFullName())
            ->setSubject($this->translator->trans('resetPassword.subject',[], 'email'))
            ->setBodyHtml($this->templateing->render('AppBundle:Email:password-forgotten.html.twig', $viewParams))
            ->setBodyText($this->templateing->render('AppBundle:Email:password-forgotten.text.twig', $viewParams));
        
        return $email;
    }
}
