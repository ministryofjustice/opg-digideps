<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Entity as EntityDir;
use AppBundle\Model as ModelDir;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;

class MailFactory
{
    const AREA_DEPUTY = 'deputy';
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
     * @var Router
     */
    protected $router;

    public function __construct(Container $container)
    {
        // validate args
        $this->container = $container;
        $this->translator = $container->get('translator');
        $this->templating = $container->get('templating');
        $this->router = $container->get('router');
    }

    /**
     * @param string $area      deputy|admin
     * @param string $routeName must be in YML config under email.routes
     * @param array  $params
     * 
     * @return string calculated route
     */
    private function generateAbsoluteLink($area, $routeName, array $params = [])
    {
        switch ($area) {
            case self::AREA_DEPUTY:
                return $this->container->getParameter('non_admin_host').$this->router->generate($routeName, $params);
            case self::AREA_ADMIN:
                return $this->container->getParameter('admin_host').$this->router->generate($routeName, $params);
            default:
                throw new \Exception("area $area not found");
        }
    }

    /**
     * @param \AppBundle\Entity\User $user
     *
     * @return \AppBundle\Model\Email
     */
    public function createActivationEmail(EntityDir\User $user)
    {
        $area = $user->getRole()['role'] == 'ROLE_ADMIN' ? self::AREA_ADMIN : self::AREA_DEPUTY;

        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $this->generateAbsoluteLink($area, 'homepage', []),
            'link' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'activate',
                'token' => $user->getRegistrationToken(),
             ]),
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

    /**
     * @param EntityDir\User $user
     *
     * @return ModelDir\Email
     */
    public function createResetPasswordEmail(EntityDir\User $user)
    {
        $area = $user->getRole()['role'] == 'ROLE_ADMIN' ? self::AREA_ADMIN : self::AREA_DEPUTY;

        $viewParams = [
            'name' => $user->getFullName(),
            'link' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'password-reset',
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

        $area = $user->getRole()['role'] == 'ROLE_ADMIN' ? self::AREA_ADMIN : self::AREA_DEPUTY;

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
     * @param EntityDir\User          $user
     * @param EntityDir\Report\Report $report
     * @param $pdfBinaryContent
     *
     * @return ModelDir\Email
     */
    public function createReportEmail(EntityDir\User $user, EntityDir\Report\Report $report, $pdfBinaryContent)
    {
        $email = new ModelDir\Email();

        $area = $user->getRole()['role'] == 'ROLE_ADMIN' ? self::AREA_ADMIN : self::AREA_DEPUTY;

        $viewParams = [
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage'),
        ];

        $client = $report->getClient();
        $attachmentName = sprintf('DigiRep-%s_%s_%s.pdf',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', //some old reports have no submission date
            $client->getCaseNumber()
        );

        $email
            ->setFromEmail($this->container->getParameter('email_report_submit')['from_email'])
            ->setFromName($this->translate('reportSubmission.fromName'))
            ->setToEmail($this->container->getParameter('email_report_submit')['to_email'])
            ->setToName($this->translate('reportSubmission.toName'))
            ->setSubject($this->translate('reportSubmission.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:report-submission.html.twig', $viewParams))
            ->setAttachments([new ModelDir\EmailAttachment($attachmentName, 'application/pdf', $pdfBinaryContent)]);

        return $email;
    }

    /**
     * @param EntityDir\User          $user
     * @param EntityDir\Report\Report $odr
     * @param $pdfBinaryContent
     *
     * @return ModelDir\Email
     */
    public function createOdrEmail(EntityDir\User $user, EntityDir\Odr\Odr $odr, $pdfBinaryContent)
    {
        $email = new ModelDir\Email();

        $area = $user->getRole()['role'] == 'ROLE_ADMIN' ? self::AREA_ADMIN : self::AREA_DEPUTY;

        $viewParams = [
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage'),
        ];

        $client = $odr->getClient();
        $attachmentName = sprintf('DigiOdrRep-%s_%s.pdf',
            $odr->getSubmitDate() ? $odr->getSubmitDate()->format('Y-m-d') : 'n-a-',
            $client->getCaseNumber()
        );

        $email
            ->setFromEmail($this->container->getParameter('email_report_submit')['from_email'])
            ->setFromName($this->translate('odrSubmission.fromName'))
            ->setToEmail($this->container->getParameter('email_report_submit')['to_email'])
            ->setToName($this->translate('odrSubmission.toName'))
            ->setSubject($this->translate('odrSubmission.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:odr-submission.html.twig', $viewParams))
            ->setAttachments([new ModelDir\EmailAttachment($attachmentName, 'application/pdf', $pdfBinaryContent)]);

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
 * @param EntityDir\User          $user
 * @param EntityDir\Report\Report $submittedReport
 * @param EntityDir\Report        $newReport
 *
 * @return ModelDir\Email
 */
    public function createReportSubmissionConfirmationEmail(EntityDir\User $user, EntityDir\Report\Report $submittedReport, EntityDir\Report\Report $newReport)
    {
        $email = new ModelDir\Email();

        $viewParams = [
            'submittedReport' => $submittedReport,
            'newReport' => $newReport,
            'link' => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'reports', [
                'cot' => EntityDir\Report\Report::PROPERTY_AND_AFFAIRS, //TODO take from $submittedReport ?
            ]),
            'homepageUrl' => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'homepage'),
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
     * @param EntityDir\User          $user
     * @param EntityDir\Odr\Odr       $odr
     * @param EntityDir\Report        $newReport
     *
     * @return ModelDir\Email
     */
    public function createOdrSubmissionConfirmationEmail(EntityDir\User $user, EntityDir\Odr\Odr $odr)
    {
        $email = new ModelDir\Email();

        $viewParams = [
            'homepageUrl' => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'homepage'),
        ];

        $email
            ->setFromEmail($this->container->getParameter('email_send')['from_email'])
            ->setFromName($this->translate('odrSubmissionConfirmation.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFirstname())
            ->setSubject($this->translate('odrSubmissionConfirmation.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:odr-submission-confirm.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:odr-submission-confirm.text.twig', $viewParams));

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
