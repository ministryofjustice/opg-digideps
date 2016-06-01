<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Entity as EntityDir;
use AppBundle\Model as ModelDir;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;

class MailFactory
{
    const AREA_FRONTEND = 'frontend';
    const AREA_ADMIN = 'admin';

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
    protected $roleToArea;

    /**
     * @var array
     */
    private static $allowedAreas = [self::AREA_FRONTEND, self::AREA_ADMIN];

    public function __construct(Container $container, array $roleToArea)
    {
        // validate args
        $this->roleToArea = $roleToArea;
        foreach ($roleToArea as $area) {
            if (!in_array($area, self::$allowedAreas)) {
                throw new \InvalidArgumentException("Area $area not valid");
            }
        }

        $this->container = $container;
        $this->translator = $container->get('translator');
        $this->templating = $container->get('templating');
    }

    /**
     * @param string $area      frontend|admin
     * @param string $routeName must be in YML config under email.routes
     * @param array  $params
     * 
     * @return string calculated route
     */
    private function generateAbsoluteLink($area, $routeName, array $params = [])
    {
        if (!in_array($area, self::$allowedAreas)) {
            throw new \InvalidArgumentException(__METHOD__.": area must be frontend or admin, $area given");
        }
        $baseUrl = trim($this->container->getParameter('email')['base_url'][$area]);

        $route = $this->container->getParameter('email')['routes'][$routeName];

        // prepare str_replace args to build route
        $search = [];
        $replace = [];
        foreach ($params as $k => $v) {
            $search[] = '{'.$k.'}';
            $replace[] = $v;
        }

        return $baseUrl.str_replace($search, $replace, $route);
    }

    private function getAreaFromUserRole(EntityDir\User $user)
    {
        $role = $user->getRole()->getRole();
        if (empty($this->roleToArea[$role])) {
            throw new \RuntimeException(__METHOD__." : area not defined for user $role");
        }

        return $this->roleToArea[$role];
    }

    public function createActivationEmail(EntityDir\User $user)
    {
        /*
         * Email is sent from admin site. If this email is sent to a deputy, then
         * host url should for deputy site else for admin site
         **/
        $area = $this->getAreaFromUserRole($user);

        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $this->generateAbsoluteLink($area, 'homepage', []),
            'link' => $this->generateAbsoluteLink($area, 'user_activate', ['token' => $user->getRegistrationToken()]),
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage'),
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
            'link' => $this->generateAbsoluteLink($area, 'password_reset', [
                'token' => $user->getRegistrationToken(),
            ]),
            'domain' => $this->generateAbsoluteLink($area, 'homepage'),
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage'),
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
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage'),
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
     *
     * @return ModelDir\Email
     */
    public function createReportEmail(EntityDir\User $user, EntityDir\Report $report, $reportContent)
    {
        $email = new ModelDir\Email();

        $area = $this->getAreaFromUserRole($user);

        $viewParams = [
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage'),
        ];
        
        $client = $report->getClient();
        $attachmentName = 'report-'.$client->getCaseNumber().'.pdf';
//        $attachmentName = 'DigiRep-2016_2016-05-24_'.$client->getCaseNumber().'.pdf';
        
        $email
            ->setFromEmail($this->container->getParameter('email_report_submit')['from_email'])
            ->setFromName($this->translate('reportSubmission.fromName'))
            ->setToEmail($this->container->getParameter('email_report_submit')['to_email'])
            ->setToName($this->translate('reportSubmission.toName'))
            ->setSubject($this->translate('reportSubmission.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:report-submission.html.twig', $viewParams))
            ->setAttachments([new ModelDir\EmailAttachment($attachmentName, 'application/pdf', $reportContent)]);

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
            'response' => $response,
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

        $viewParams = [
            'submittedReport' => $submittedReport,
            'newReport' => $newReport,
            'link' => $this->generateAbsoluteLink(self::AREA_FRONTEND, 'client_home'),
            'homepageUrl' => $this->generateAbsoluteLink(self::AREA_FRONTEND, 'homepage'),
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
