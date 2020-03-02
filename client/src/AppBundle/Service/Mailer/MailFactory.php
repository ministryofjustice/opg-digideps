<?php declare(strict_types=1);

namespace AppBundle\Service\Mailer;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Model as ModelDir;
use AppBundle\Model\FeedbackReport;
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
    const REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID = '2f8fff09-5a71-446a-a220-d8a3dc78fa42';
    const NDR_SUBMITTED_CONFIRMATION_TEMPLATE_ID = '96fcb7e1-d80f-4e0e-80c8-2c1237af8b10';

    const NOTIFY_FROM_EMAIL_ID = 'db930cb2-2153-4e2a-b3d0-06f7c7f92f37';

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
            ->setFromEmail($this->emailParams['from_email'])
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

        $notifyParams = [
            'resetLink' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'password-reset',
                'token'  => $user->getRegistrationToken(),
            ]),
        ];

        return (new ModelDir\Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translate('resetPassword.fromName'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFullName())
            ->setSubject($this->translate('resetPassword.subject'))
            ->setTemplate(self::RESET_PASSWORD_TEMPLATE_ID)
            ->setParameters($notifyParams);
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
     * @param array $response
     * @param bool $isPostSubmission
     * @param User|null $user
     * @return ModelDir\Email
     */
    public function createGeneralFeedbackEmail($response)
    {
        $notifyParams = [
            'comments' => !empty($response['comments']) ? $response['comments'] : 'Not provided',
            'name' => !empty($response['name']) ? $response['name'] : 'Not provided',
            'phone' => !empty($response['phone']) ? $response['phone'] : 'Not provided',
            'page' => !empty($response['page']) ? $response['page'] : 'Not provided',
            'email' => !empty($response['email']) ? $response['email'] : 'Not provided',
            'satisfactionLevel' => !empty($response['satisfactionLevel']) ? $response['satisfactionLevel'] : 'Not provided',
            'subject' => $this->translate('feedbackForm.subject'),
        ];

        return (new ModelDir\Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translate('feedbackForm.fromName'))
            ->setToEmail($this->emailParams['feedback_send_to_address'])
            ->setToName($this->translate('feedbackForm.toName'))
            ->setTemplate(self::GENERAL_FEEDBACK_TEMPLATE_ID)
            ->setParameters($notifyParams);
    }

    public function createPostSubmissionFeedbackEmail(FeedbackReport $response, User $user)
    {
        $notifyParams = [
            'comments' => $response->getComments() ? $response->getComments() : 'Not provided',
            'name' => $user->getFullName(),
            'phone' => $user->getPhoneMain(),
            'email' => $user->getEmail(),
            'satisfactionLevel' => $response->getSatisfactionLevel() ? $response->getSatisfactionLevel() : 'Not provided',
            'userRole' => $user->getRoleFullName(),
            'subject' => $this->translate('feedbackForm.subject'),
        ];

        return (new ModelDir\Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translate('feedbackForm.fromName'))
            ->setToEmail($this->emailParams['feedback_send_to_address'])
            ->setToName($this->translate('feedbackForm.toName'))
            ->setTemplate(self::POST_SUBMISSION_FEEDBACK_TEMPLATE_ID)
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
            ->setToEmail($this->emailParams['update_send_to_address'])
            ->setToName($this->translate('addressUpdateForm.' . $type . '.toName'))
            ->setSubject($this->translate('addressUpdateForm.' . $type . '.subject'))
            ->setBodyHtml($this->templating->render($template, $viewParams));

        return $email;
    }

    /**
     * @param User $user
     * @param EntityDir\ReportInterface $submittedReport
     * @param EntityDir\Report\Report $newReport
     *
     * @return ModelDir\Email
     * @throws \Exception
     */
    public function createReportSubmissionConfirmationEmail(User $user, EntityDir\ReportInterface $submittedReport, EntityDir\Report\Report $newReport)
    {
        /** @var ModelDir\Email $email */
        $email = (new ModelDir\Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translator->trans('reportSubmissionConfirmation.fromName', [], 'email'))
            ->setToEmail($user->getEmail())
            ->setTemplate(self::REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID);

        /** @var \DateTime $dateSubmittableFrom */
        $dateSubmittableFrom = clone $submittedReport->getEndDate();
        $dateSubmittableFrom->add(new \DateInterval('P1D'));

        /** @var array $notifyParams */
        $notifyParams = [
            'clientFullname' => $submittedReport->getClient()->getFullname(),
            'deputyFullname' => $user->getFullName(),
            'orgIntro' => self::getRecipientRole($user) == 'default' ? '' : $this->buildOrgIntroText($submittedReport->getClient()),
            'startDate' => $submittedReport->getStartDate()->format('d/m/Y'),
            'endDate' => $submittedReport->getEndDate()->format('d/m/Y'),
            'homepageURL' => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'homepage'),
            'newStartDate' => $newReport->getStartDate()->format('d/m/Y'),
            'newEndDate' => $newReport->getEndDate()->format('d/m/Y'),
            'EndDatePlus1' => $dateSubmittableFrom->format('d/m/Y'),
            'PFA' => substr($submittedReport->getType(), 0, 3 ) === '104' ? 'no' : 'yes',
            'lay' => $user->isLayDeputy() ? 'yes' : 'no'
        ];

        $email->setParameters($notifyParams);

        return $email;
    }

    /**
     * @param EntityDir\Client $client
     * @return string
     */
    private function buildOrgIntroText(EntityDir\Client $client): string
    {
        return $this->translator->trans(
            'caseDetails',
            ['%fullClientName%' => $client->getFullname(), '%caseNumber%' => $client->getCaseNumber()],
            'email-report-submission-confirm'
        );
    }

    /**
     * @param User $user
     * @param EntityDir\Ndr\Ndr $ndr
     * @param Report $report
     * @return ModelDir\Email
     * @throws \Exception
     */
    public function createNdrSubmissionConfirmationEmail(User $user, EntityDir\Ndr\Ndr $ndr, Report $report)
    {
        /** @var ModelDir\Email $email */
        $email = (new ModelDir\Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translator->trans('ndrSubmissionConfirmation.fromName', [], 'email'))
            ->setToEmail($user->getEmail())
            ->setTemplate(self::NDR_SUBMITTED_CONFIRMATION_TEMPLATE_ID);

        /** @var \DateTime $dateSubmittableFrom */

        $dateSubmittableFrom = clone $report->getEndDate();
        $dateSubmittableFrom->add(new \DateInterval('P1D'));

        /** @var array $notifyParams */
        $notifyParams = [
            'clientFullname' => $ndr->getClient()->getFullname(),
            'deputyFullname' => $user->getFullName(),
            'homepageURL' => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'homepage'),
            'startDate' => $report->getStartDate()->format('d/m/Y'),
            'endDate' => $report->getEndDate()->format('d/m/Y'),
            'EndDatePlus1' => $dateSubmittableFrom->format('d/m/Y'),
            'PFA' => 'yes',
        ];

        $email->setParameters($notifyParams);

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
}
