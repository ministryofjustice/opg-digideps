<?php

namespace AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use AppBundle\Model as ModelDir;
use Symfony\Component\DependencyInjection\Container;
use AppBundle\Entity as EntityDir;

class MailFactory
{

    /**
     * @var Translator 
     */
    protected $translator;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var Container
     */
    protected $container;
    
    /**
     * @var array
     */
    protected $emailConfig;


    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->translator = $container->get('translator');
        $this->router = $container->get('router');
        $this->validator = $container->get('validator');
        $this->templating = $container->get('templating');
        $this->emailConfig =  $this->container->getParameter('email_send');
    }

    public function createActivationEmail(EntityDir\User $user)
    {
        /**
         * Email is sent from admin site. If this email is sent to a deputy, then
         * host url should for deputy site else for admin site
         */
        if($user->getRole()['role'] == 'ROLE_ADMIN'){
            $absoluteUrl = $this->router->generate('user_activate', [ 'token' => $user->getRegistrationToken()],true);
        }else{
            $relativeUrl = $this->router->generate('user_activate', [ 'token' => $user->getRegistrationToken()]);
            $absoluteUrl = $this->container->getParameter('non_admin_host').$relativeUrl; 
        }
       
        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $this->router->generate('homepage', [], true),
            'link' => $absoluteUrl,
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
        ];
        
        $email = $this->createEmail()
            ->setFromEmail($this->emailConfig['from_email'])
            ->setFromName($this->translate('activation.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFullName())
            ->setSubject($this->translate('activation.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:user-activate.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:user-activate.text.twig', $viewParams));

        return $email;
    }

    public function createResetPasswordEmail(EntityDir\User $user)
    {
        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $this->router->generate('homepage', [], true),
            'link' => $this->router->generate('user_activate', [
                'action' => 'password-reset',
                'token' => $user->getRegistrationToken()
                ], true)
        ];

        $email = $this->createEmail()
            ->setFromEmail($this->emailConfig['from_email'])
            ->setFromName($this->translate('resetPassword.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFullName())
            ->setSubject($this->translate('resetPassword.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:password-forgotten.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:password-forgotten.text.twig', $viewParams));

        return $email;
    }

    /**
     * @param EntityDir\User $user
     * 
     * @return Email
     */
    public function createChangePasswordEmail(EntityDir\User $user)
    {
         $email = $this->createEmail()
            ->setFromEmail($this->emailConfig['from_email'])
            ->setFromName($this->translate('changePassword.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFirstname())
            ->setSubject($this->translate('changePassword.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:change-password.html.twig'));

        return $email;
    }
    
    
    /**
     * @param EntityDir\Client $client
     * @return ModelDir\Email
     */
    public function createReportEmail(EntityDir\Client $client, $reportContent)
    {
        $email = $this->createEmail()
            ->setFromEmail($this->emailConfig['from_email'])
            ->setFromName($this->translate('reportSubmission.fromName'))
            ->setToEmail($this->emailConfig['to_email'])
            ->setToName($this->translate('reportSubmission.toName'))
            ->setSubject($this->translate('reportSubmission.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:report-submission.html.twig'))
            ->setAttachments([new ModelDir\EmailAttachment('report-'.$client->getCaseNumber().'.html', 'application/xml', $reportContent)]);

        return $email;
    }
    
    /**
     * @return \AppBundle\Service\ModelDir\Email
     */
    private function createEmail()
    {
        return new ModelDir\Email();
    }
    
    private function translate($key, $vars = [])
    {
        return $this->translator->trans($key, $vars, 'email');
    }

}