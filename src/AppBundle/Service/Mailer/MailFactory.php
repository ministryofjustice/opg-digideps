<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\User;
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
                return $this->container->getParameter('non_admin_host') . $this->router->generate($routeName, $params);
            case self::AREA_ADMIN:
                return $this->container->getParameter('admin_host') . $this->router->generate($routeName, $params);
            default:
                throw new \Exception("area $area not found");
        }
    }

    /**
     * @param \AppBundle\Entity\User $user
     *
     * @return \AppBundle\Model\Email
     */
    public function createActivationEmail(User $user)
    {
        $area = $this->getUserArea($user);

        $viewParams = [
            'name'             => $user->getFullName(),
            'domain'           => $this->generateAbsoluteLink($area, 'homepage', []),
            'link'             => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'activate',
                'token'  => $user->getRegistrationToken(),
            ]),
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
            'homepageUrl'      => $this->generateAbsoluteLink($area, 'homepage'),
            'recipientRole' => self::getRecipientRole($user)
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
     * Generates the recipient Role aspect of the context string. Most users use the 'default' recipient role.
     * This maps to the translation file
     *
     * Called from BehatController to allow email-viewer to function
     *
     * @param  User   $user
     * @return string
     */
    public static function getRecipientRole(User $user)
    {
        switch ($user->getRoleName()) {
            case User::ROLE_PA_NAMED:
            case User::ROLE_PA_ADMIN:
            case User::ROLE_PA_TEAM_MEMBER:
            case User::ROLE_PROF_NAMED:
            case User::ROLE_PROF_ADMIN:
            case User::ROLE_PROF_TEAM_MEMBER:
                return $user->getRoleName();

            default:
                return 'default';
        }
    }

    /**
     * @param EntityDir\User $user
     *
     * @return ModelDir\Email
     */
    public function createResetPasswordEmail(User $user)
    {
        $area = $this->getUserArea($user);

        $viewParams = [
            'name'        => $user->getFullName(),
            'link'        => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'password-reset',
                'token'  => $user->getRegistrationToken(),
            ]),
            'domain'      => $this->generateAbsoluteLink($area, 'homepage'),
            'homepageUrl' => $this->generateAbsoluteLink($area, 'homepage'),
            'recipientRole' => self::getRecipientRole($user)
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
    public function createChangePasswordEmail(User $user)
    {
        $email = new ModelDir\Email();

        $area = $this->getUserArea($user);

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
     * Get user area depending on the role
     *
     * @param  EntityDir\User $user
     * @return string
     */
    private function getUserArea(EntityDir\User $user)
    {
        return $user->isDeputy() ? self::AREA_DEPUTY : self::AREA_ADMIN;
    }

    /**
     * @param EntityDir\User          $user
     * @param EntityDir\Report\Report $ndr
     * @param $pdfBinaryContent
     *
     * @return ModelDir\Email
     */
    public function createNdrEmail(EntityDir\User $user, EntityDir\Ndr\Ndr $ndr, $pdfBinaryContent)
    {
        $email = new ModelDir\Email();

        $viewParams = [
            'homepageUrl' => $this->generateAbsoluteLink($this->getUserArea($user), 'homepage'),
        ];

        $client = $ndr->getClient();
        $attachmentName = sprintf('DigiNdrRep-%s_%s.pdf',
            $ndr->getSubmitDate() ? $ndr->getSubmitDate()->format('Y-m-d') : 'n-a-',
            $client->getCaseNumber()
        );

        $email
            ->setFromEmail($this->container->getParameter('email_report_submit')['from_email'])
            ->setFromName($this->translate('ndrSubmission.fromName'))
            ->setToEmail($this->container->getParameter('email_report_submit')['to_email'])
            ->setToName($this->translate('ndrSubmission.toName'))
            ->setSubject($this->translate('ndrSubmission.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:ndr-submission.html.twig', $viewParams))
            ->setAttachments([new ModelDir\EmailAttachment($attachmentName, 'application/pdf', $pdfBinaryContent)]);

        return $email;
    }

    /**
     * @param string $response
     *
     * @return ModelDir\Email
     */
    public function createFeedbackEmail($response, EntityDir\User $user)
    {
        $viewParams = [
            'response' => $response,
            'userRole' => $user->getRoleFullName()
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
     * @param string $response
     *
     * @return ModelDir\Email
     */
    public function createAddressUpdateEmail($response, EntityDir\User $user)
    {
        $viewParams = [
            'response' => $response,
            'userRole' => $user->getRoleFullName()
        ];

        $email = new ModelDir\Email();
        $email
            ->setFromEmail($this->container->getParameter('address_update_send')['from_email'])
            ->setFromName($this->translate('addressUpdateForm.fromName'))
            ->setToEmail($this->container->getParameter('address_update_send')['to_email'])
            ->setToName($this->translate('addressUpdateForm.toName'))
            ->setSubject($this->translate('addressUpdateForm.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:address-update.html.twig', $viewParams));

        return $email;
    }

    /**
     * @param EntityDir\User          $user
     * @param EntityDir\Report\Report $submittedReport
     * @param EntityDir\Report        $newReport
     *
     * @return ModelDir\Email
     */
    public function createReportSubmissionConfirmationEmail(EntityDir\User $user, EntityDir\ReportInterface $submittedReport, EntityDir\Report\Report $newReport)
    {
        $email = new ModelDir\Email();

        $viewParams = [
            'submittedReport' => $submittedReport,
            'newReport'       => $newReport,
            'fullDeputyName'  => $user->getFullName(),
            'fullClientName'  => $submittedReport->getClient()->getFullname(),
            'caseNumber'      => $submittedReport->getClient()->getCaseNumber(),
            'homepageUrl'     => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'homepage'),
            'recipientRole'   => self::getRecipientRole($user)
        ];

        $email
            ->setFromEmail($this->container->getParameter('email_send')['from_email'])
            ->setFromName($this->translate('reportSubmissionConfirmation.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFirstname())
            ->setSubject($this->translate('reportSubmissionConfirmation.subject', ['%clientFullname%' => $submittedReport->getClient()->getFullname()]))
            ->setBodyHtml($this->templating->render('AppBundle:Email:report-submission-confirm.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:report-submission-confirm.text.twig', $viewParams));

        return $email;
    }

    /**
     * @param EntityDir\User          $user
     * @param EntityDir\Report\Report $submittedReport
     * @param EntityDir\Report        $newReport
     * @param $pdfBinaryContent
     *
     * @return ModelDir\Email
     */
    public function createOrgReportSubmissionConfirmationEmail(EntityDir\User $user, EntityDir\ReportInterface $submittedReport, EntityDir\ReportInterface $newReport)
    {
        $email = $this->createReportSubmissionConfirmationEmail($user, $submittedReport, $newReport);

        return $email;
    }

    /**
     * @param EntityDir\User    $user
     * @param EntityDir\Ndr\Ndr $ndr
     * @param EntityDir\Report  $newReport
     *
     * @return ModelDir\Email
     */
    public function createNdrSubmissionConfirmationEmail(EntityDir\User $user, EntityDir\Ndr\Ndr $ndr)
    {
        $email = new ModelDir\Email();

        $viewParams = [
            'homepageUrl'     => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'homepage'),
            'deputyFirstName' => $user->getFirstname() . ' ' . $user->getLastname(),
            'recipientRole'   => self::getRecipientRole($user)
        ];

        $email
            ->setFromEmail($this->container->getParameter('email_send')['from_email'])
            ->setFromName($this->translate('ndrSubmissionConfirmation.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFirstname())
            ->setSubject($this->translate('ndrSubmissionConfirmation.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:ndr-submission-confirm.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:ndr-submission-confirm.text.twig', $viewParams));

        return $email;
    }

    /**
     * @param string $key
     * @param array $params
     *
     * @return string
     */
    private function translate($key, $params = [])
    {
        return $this->translator->trans($key, $params, 'email');
    }

    /**
     * @param  EntityDir\Report\Report $report
     * @return string
     */
    public function getReportAttachmentName(EntityDir\Report\Report $report)
    {
        $client = $report->getClient();
        $attachmentName = sprintf('DigiRep-%s_%s_%s.pdf',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', //some old reports have no submission date
            $client->getCaseNumber()
        );
        return $attachmentName;
    }

    /**
     * @param \AppBundle\Entity\User $user
     *
     * @return \AppBundle\Model\Email
     */
    public function createCoDeputyInvitationEmail(User $invitedUser, User $loggedInUser)
    {
        $area = $this->getUserArea($loggedInUser);

        $viewParams = [
            'deputyName'  => $loggedInUser->getFirstname() . ' ' . $loggedInUser->getLastname(),
            'domain'           => $this->generateAbsoluteLink($area, 'homepage', []),
            'link'             => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'activate',
                'token'  => $invitedUser->getRegistrationToken(),
            ]),
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
            'homepageUrl'      => $this->generateAbsoluteLink($area, 'homepage'),
            'recipientRole' => self::getRecipientRole($loggedInUser)
        ];

        $email = new ModelDir\Email();

        $email
            ->setFromEmail($this->container->getParameter('email_send')['from_email'])
            ->setFromName($this->translate('codeputyInvitation.fromName'))
            ->setToEmail($invitedUser->getEmail())
            ->setToName($invitedUser->getFullName())
            ->setSubject($this->translate('codeputyInvitation.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:coDeputy-invitation.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:coDeputy-invitation.text.twig', $viewParams));

        return $email;
    }
}
