<?php declare(strict_types=1);

namespace AppBundle\Service\Mailer;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\User;
use AppBundle\Model as ModelDir;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class MailFactory
{
    const AREA_DEPUTY = 'deputy';
    const AREA_ADMIN = 'admin';

    // Maintained in GOVUK Notify
    const RESET_PASSWORD_TEMPLATE_ID = 'e7312e62-2602-4903-89e6-93ad943bacb1';
    const POST_SUBMISSION_FEEDBACK_TEMPLATE_ID = '862f1ce7-bde5-4397-be68-bd9e4537cff0';
    const GENERAL_FEEDBACK_TEMPLATE_ID = '63a25dfa-116f-4991-b7c4-35a79ac5061e';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $emailParams;

    /**
     * @var array
     */
    private $baseURLs;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        EngineInterface $templating,
        array $emailParams,
        array $baseURLs
    )
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->templating = $templating;
        $this->emailParams = $emailParams;
        $this->baseURLs = $baseURLs;
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
                return $this->baseURLs['front'] . $this->router->generate($routeName, $params);
            case self::AREA_ADMIN:
                return $this->baseURLs['admin'] . $this->router->generate($routeName, $params);
            default:
                throw new \Exception("area $area not found");
        }
    }

    /**
     * @param User $user
     *
     * @return \AppBundle\Model\Email
     */
    public function createActivationEmail(User $user)
    {
        $area = $this->getUserArea($user);
        $homepageURL = $this->generateAbsoluteLink($area, 'homepage');

        $viewParams = [
            'name'             => $user->getFullName(),
            'domain'           => $homepageURL,
            'link'             => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'activate',
                'token'  => $user->getRegistrationToken(),
            ]),
            'tokenExpireHours' => User::TOKEN_EXPIRE_HOURS,
            'homepageUrl'      => $homepageURL,
            'recipientRole' => self::getRecipientRole($user)
        ];

        $email = new ModelDir\Email();

        $email
            ->setFromEmail($this->emailParams['from_email'])
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
     * @todo Remove createResetPasswordEmail in favour of this once we're happy with Notify
     * @param User $user
     * @param array $emailSendParams
     *
     * @return ModelDir\Email
     * @throws \Exception
     */
    public function createResetPasswordEmailNotify(User $user)
    {
        $area = $this->getUserArea($user);

        $notifyParameters = [
            'resetLink' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'password-reset',
                'token'  => $user->getRegistrationToken(),
            ]),
        ];

        return (new ModelDir\Email())
            ->setFromEmailNotifyID($this->emailParams['from_email_notify_id'])
            ->setFromName($this->translate('resetPassword.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFullName())
            ->setSubject($this->translate('resetPassword.subject'))
            ->setTemplate(self::RESET_PASSWORD_TEMPLATE_ID)
            ->setParameters($notifyParameters);
    }

    /**
     * @param User $user
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
            ->setFromEmail($this->emailParams['from_email'])
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
     * @param User $user
     * @return string
     */
    private function getUserArea(User $user)
    {
        return $user->isDeputy() ? self::AREA_DEPUTY : self::AREA_ADMIN;
    }

    /**
     * @param User $user
     * @param EntityDir\Report\Report $ndr
     * @param $pdfBinaryContent
     *
     * @return ModelDir\Email
     */
    public function createNdrEmail(User $user, EntityDir\Ndr\Ndr $ndr, $pdfBinaryContent)
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
            ->setFromEmail($this->emailParams['from_email'])
            ->setFromName($this->translate('ndrSubmission.fromName'))
            ->setToEmail($this->emailParams['email_report_submit_to_email'])
            ->setToName($this->translate('ndrSubmission.toName'))
            ->setSubject($this->translate('ndrSubmission.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:ndr-submission.html.twig', $viewParams))
            ->setAttachments([new ModelDir\EmailAttachment($attachmentName, 'application/pdf', $pdfBinaryContent)]);

        return $email;
    }

    /**
     * @param array $response
     * @param bool $isPostSubmission
     * @param User|null $user
     * @return ModelDir\Email
     */
    public function createFeedbackEmail($response, bool $isPostSubmission, User $user = null)
    {
        $notifyParams = [
            'comments' => !empty($response['comments']) ? $response['comments'] : 'Not provided',
            'name' => !empty($response['name']) ? $response['name'] : 'Not provided',
            'phone' => !empty($response['phone']) ? $response['phone'] : 'Not provided',
            'page' => !empty($response['page']) ? $response['page'] : 'Not provided',
            'email' => !empty($response['email']) ? $response['email'] : 'Not provided',
            'satisfactionLevel' => !empty($response['satisfactionLevel']) ? $response['satisfactionLevel'] : 'Not provided',
            'userRole' => $user ? $user->getRoleFullName() : 'Not provided',
            'subject' => $this->translate('feedbackForm.subject'),
        ];

        $templateID = $isPostSubmission ? self::POST_SUBMISSION_FEEDBACK_TEMPLATE_ID : self::GENERAL_FEEDBACK_TEMPLATE_ID;

        return (new ModelDir\Email())
            ->setFromEmailNotifyID($this->emailParams['from_email_notify_id'])
            ->setFromName($this->translate('feedbackForm.fromName'))
            ->setToEmail($this->emailParams['email_feedback_send_to_email'])
            ->setToName($this->translate('feedbackForm.toName'))
            ->setTemplate($templateID)
            ->setParameters($notifyParams);
    }


    /**
     * @param string $response
     *
     * @return ModelDir\Email
     */
    public function createAddressUpdateEmail($response, User $user, $type)
    {
        if ($type === 'deputy') {
            $countryCode = $response->getAddressCountry();
        } else {
            $countryCode = $response->getCountry();
        }

        $countryName = Intl::getRegionBundle()->getCountryName($countryCode);

        $viewParams = [
            'response' => $response,
            'countryName' => $countryName,
            'caseNumber' => $user->getClients()[0]->getCaseNumber(),
            'userRole' => $user->getRoleFullName()
        ];

        $template = 'AppBundle:Email:address-update-' . $type . '.html.twig';

        $email = new ModelDir\Email();
        $email
            ->setFromEmail($this->emailParams['from_email'])
            ->setFromName($this->translate('addressUpdateForm.' . $type . '.fromName'))
            ->setToEmail($this->emailParams['email_update_send_to_email'])
            ->setToName($this->translate('addressUpdateForm.' . $type . '.toName'))
            ->setSubject($this->translate('addressUpdateForm.' . $type . '.subject'))
            ->setBodyHtml($this->templating->render($template, $viewParams));

        return $email;
    }

    /**
     * @param User $user
     * @param EntityDir\Report\Report $submittedReport
     * @param EntityDir\Report        $newReport
     *
     * @return ModelDir\Email
     */
    public function createReportSubmissionConfirmationEmail(User $user, EntityDir\ReportInterface $submittedReport, EntityDir\Report\Report $newReport)
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
            ->setFromEmail($this->emailParams['from_email'])
            ->setFromName($this->translate('reportSubmissionConfirmation.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFirstname())
            ->setSubject($this->translate('reportSubmissionConfirmation.subject', ['%clientFullname%' => $submittedReport->getClient()->getFullname()]))
            ->setBodyHtml($this->templating->render('AppBundle:Email:report-submission-confirm.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:report-submission-confirm.text.twig', $viewParams));

        return $email;
    }

    /**
     * @param User $user
     * @param EntityDir\Report\Report $submittedReport
     * @param EntityDir\Report        $newReport
     * @param $pdfBinaryContent
     *
     * @return ModelDir\Email
     */
    public function createOrgReportSubmissionConfirmationEmail(User $user, EntityDir\ReportInterface $submittedReport, EntityDir\ReportInterface $newReport)
    {
        $email = $this->createReportSubmissionConfirmationEmail($user, $submittedReport, $newReport);

        return $email;
    }

    /**
     * @param User $user
     * @param EntityDir\Ndr\Ndr $ndr
     * @param EntityDir\Report  $newReport
     *
     * @return ModelDir\Email
     */
    public function createNdrSubmissionConfirmationEmail(User $user, EntityDir\Ndr\Ndr $ndr)
    {
        $email = new ModelDir\Email();

        $viewParams = [
            'homepageUrl'     => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'homepage'),
            'deputyFirstName' => $user->getFirstname() . ' ' . $user->getLastname(),
            'recipientRole'   => self::getRecipientRole($user)
        ];

        $email
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
     * @param User $user
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
            'tokenExpireHours' => User::TOKEN_EXPIRE_HOURS,
            'homepageUrl'      => $this->generateAbsoluteLink($area, 'homepage'),
            'recipientRole' => self::getRecipientRole($loggedInUser)
        ];

        $email = new ModelDir\Email();

        $email
            ->setFromEmail($this->emailParams['from_email'])
            ->setFromName($this->translate('codeputyInvitation.fromName'))
            ->setToEmail($invitedUser->getEmail())
            ->setToName($invitedUser->getFullName())
            ->setSubject($this->translate('codeputyInvitation.subject'))
            ->setBodyHtml($this->templating->render('AppBundle:Email:coDeputy-invitation.html.twig', $viewParams))
            ->setBodyText($this->templating->render('AppBundle:Email:coDeputy-invitation.text.twig', $viewParams));

        return $email;
    }

    /**
     * @param array $response
     * @param User $user
     * @return array
     */
    private function buildNotifyParams(array $response, User $user)
    {
        return [
            'comments' => !empty($response['comments']) ? $response['comments'] : 'Not set',
            'name' => !empty($response['name']) ? $response['name'] : 'Not set',
            'phone' => !empty($response['phone']) ? $response['phone'] : 'Not set',
            'page' => !empty($response['page']) ? $response['page'] : 'Not set',
            'email' => !empty($response['email']) ? $response['email'] : 'Not set',
            'satisfaction' => !empty($response['satisfactionLevel']) ? $response['satisfactionLevel'] : 'Not set',
            'userRole' => $user ? $user->getRoleFullName() : 'Not set',
            'subject' => $this->translate('feedbackForm.subject'),
        ];
    }
}
