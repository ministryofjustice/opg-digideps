<?php
namespace AppBundle\Service\Mailer;

use AppBundle\Entity as EntityDir;
use AppBundle\Model as ModelDir;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;


class MailFactory
{
    private $routes = [];
    
    
    /**
     * @var Translator 
     */
    protected $translator;


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
        $this->templating = $container->get('templating');
    }

    /**
     * 
     * @param string $area frontend|admin
     * @param string $routeName must be in YML config under email.routes
     * @param array $params
     * 
     * @return string calculated route
     */
    private function generateAbsoluteLink($area, $routeName, array $params = [])
    {
        if (!in_array($area, ['frontend', 'admin'])) {
            throw new \InvalidArgumentException(__METHOD__ . ": area must be frontend or admin, $area given");
        }
        $baseUrl = $this->container->getParameter('email')['base_url'][$area];
        
        $route = $this->container->getParameter('email')['routes'][$routeName];
        
        // prepare str_replace args to build route
        $search = [];
        $replace = [];
        foreach ($params as $k=>$v) {
            $search[] = '{' . $k . '}';
            $replace[] = $v;
        }
        
        return $baseUrl . str_replace($search, $replace, $route);
    }
    
    private function getAreaFromUserRole(EntityDir\User $user)
    {
        return $user->getRole()->getRole() == 'ROLE_ADMIN' ? 'admin' : 'frontend';
    }
    
    public function createActivationEmail(EntityDir\User $user)
    {
        /**
         * Email is sent from admin site. If this email is sent to a deputy, then
         * host url should for deputy site else for admin site
         **/
        $area = $this->getAreaFromUserRole($user);
       
        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $this->generateAbsoluteLink($area, 'homepage', []),
            'link' => $this->generateAbsoluteLink($area, 'user_activate', [ 'token' => $user->getRegistrationToken()]),
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage')
        ];

        $email = new ModelDir\Email();
        
        $email
            ->setFromEmail($this->container->getParameter('email_send')['from_email'])
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
        $area = $this->getAreaFromUserRole($user);
        
        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $this->generateAbsoluteLink($area, 'homepage'),
            'link' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'password-reset',
                'token' => $user->getRegistrationToken()
                ], true),
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage')
        ];

        $email = new ModelDir\Email();
        
        $email
            ->setFromEmail($this->container->getParameter('email_send')['from_email'])
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
     * @return ModelDir\Email
     */
    public function createChangePasswordEmail(EntityDir\User $user)
    {
        $email = new ModelDir\Email();
        
        $area = $this->getAreaFromUserRole($user);
        
        $viewParams = [
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage')
        ];
        
        $email
            ->setFromEmail($this->container->getParameter('email_send')['from_email'])
            ->setFromName($this->translate('changePassword.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFirstname())
            ->setSubject($this->translate('changePassword.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:change-password.html.twig', $viewParams));

        return $email;
    }

    /**
     * @param EntityDir\Client $client
     * @return ModelDir\Email
     */
    public function createReportEmail(EntityDir\User $user,EntityDir\Client $client, $reportContent)
    {
        $email = new ModelDir\Email();
        
        $area = $this->getAreaFromUserRole($user);
        
        $viewParams = [
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage')
        ];
        
        $email
            ->setFromEmail($this->container->getParameter('email_report_submit')['from_email'])
            ->setFromName($this->translate('reportSubmission.fromName'))
            ->setToEmail($this->container->getParameter('email_report_submit')['to_email'])
            ->setToName($this->translate('reportSubmission.toName'))
            ->setSubject($this->translate('reportSubmission.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:report-submission.html.twig', $viewParams))
            ->setAttachments([new ModelDir\EmailAttachment('report-' . $client->getCaseNumber() . '.html', 'application/xml', $reportContent)]);

        return $email;
    }

    /**
     * @param string $response
     * 
     * @return ModelDir\Email
     */
    public function createFeedbackEmail($response)
    {
        $viewParams = [ 
            'response' => $response
         ];
        
        $email = new ModelDir\Email();
        $email
            ->setFromEmail($this->container->getParameter('email_feedback_send')['from_email'])
            ->setFromName($this->translate('feedbackForm.fromName'))
            ->setToEmail($this->container->getParameter('email_feedback_send')['to_email'])
            ->setToName($this->translate('feedbackForm.toName'))
            ->setSubject($this->translate('feedbackForm.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:feedback.html.twig', $viewParams));

        return $email;
    }

    /**
     * @param EntityDir\User $user
     * 
     * @return ModelDir\Email
     */
    public function createReportSubmissionConfirmationEmail(EntityDir\User $user, EntityDir\Report $submittedReport, EntityDir\Report $newReport)
    {
        $email = new ModelDir\Email();
        $area = 'frontend'; //only deputy submit reports
        
        $viewParams = [
            'submittedReport'=> $submittedReport,
            'newReport' => $newReport,
            'link' => $this->generateAbsoluteLink($area, 'report_overview', [ 'reportId' => $newReport->getId()]),
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage')
        ];
        
        $email
            ->setFromEmail($this->container->getParameter('email_send')['from_email'])
            ->setFromName($this->translate('reportSubmissionConfirmation.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFirstname())
            ->setSubject($this->translate('reportSubmissionConfirmation.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:report-submission-confirm.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:report-submission-confirm.text.twig', $viewParams));

        return $email;
    }
    
    /**
     * @param string $key
     * 
     * @return string
     */
    private function translate($key)
    {
        return $this->translator->trans($key, [], 'email');
    }

}