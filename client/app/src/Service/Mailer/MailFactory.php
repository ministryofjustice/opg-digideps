<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use App\Entity as EntityDir;
use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Model\Email;
use App\Model\FeedbackReport;
use App\Service\IntlService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailFactory
{
    public const AREA_DEPUTY = 'deputy';
    public const AREA_ADMIN = 'admin';

    // Maintained in GOVUK Notify
    public const ACTIVATION_TEMPLATE_ID = '07e7fdb3-ad81-4105-b6b6-c3854e0c6caa';
    public const GENERAL_FEEDBACK_TEMPLATE_ID = '63a25dfa-116f-4991-b7c4-35a79ac5061e';
    public const REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID = '2f8fff09-5a71-446a-a220-d8a3dc78fa42';
    public const NDR_SUBMITTED_CONFIRMATION_TEMPLATE_ID = '96fcb7e1-d80f-4e0e-80c8-2c1237af8b10';
    public const CLIENT_DETAILS_CHANGE_TEMPLATE_ID = '258aaf2d-076b-4b5c-a386-f3551c5f3945';
    public const DEPUTY_DETAILS_CHANGE_TEMPLATE_ID = '6469b39b-6ace-4f93-9e80-6152627e0d36';
    public const INVITATION_LAY_TEMPLATE_ID = 'b8afb0d0-c8e5-4191-bce7-74ba91c74cad';
    public const INVITATION_ORG_TEMPLATE_ID = 'd410fce7-ce00-46eb-824d-82f998a437a4';
    public const POST_SUBMISSION_FEEDBACK_TEMPLATE_ID = '862f1ce7-bde5-4397-be68-bd9e4537cff0';
    public const RESET_PASSWORD_TEMPLATE_ID = '827555cc-498a-43ef-957a-63fa387065e3';
    public const PROCESS_ORG_CSV_TEMPLATE_ID = 'ce20ca97-a954-4d34-8a21-8b4f156188a8';
    public const PROCESS_LAY_CSV_TEMPLATE_ID = '1e6fddc4-999d-4c44-8038-1853ea0e8511';

    public const NOTIFY_FROM_EMAIL_ID = 'db930cb2-2153-4e2a-b3d0-06f7c7f92f37';

    public const DATE_FORMAT = 'j F Y';

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

    /**
     * @var IntlService
     */
    private $intlService;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        IntlService $intlService,
        array $emailParams,
        array $baseURLs
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->emailParams = $emailParams;
        $this->baseURLs = $baseURLs;
        $this->intlService = $intlService;
    }

    /**
     * @param string $area      deputy|admin
     * @param string $routeName must be in YML config under email.routes
     *
     * @return string calculated route
     */
    private function generateAbsoluteLink(string $area, string $routeName, array $params = [])
    {
        switch ($area) {
            case self::AREA_DEPUTY:
                return $this->baseURLs['front'].$this->router->generate($routeName, $params);
            case self::AREA_ADMIN:
                return $this->baseURLs['admin'].$this->router->generate($routeName, $params);
            default:
                throw new \Exception("area $area not found");
        }
    }

    private function getContactParameters(User $user): array
    {
        if ($user->isLayDeputy()) {
            $emailKey = 'layDeputySupportEmail';
            $phoneKey = 'helpline';
        } elseif ($user->isDeputyPa()) {
            $emailKey = 'paSupportEmail';
            $phoneKey = 'helplinePA';
        } elseif ($user->isDeputyProf()) {
            $emailKey = 'profSupportEmail';
            $phoneKey = 'helplineProf';
        } else {
            $emailKey = 'generalSupportEmail';
            $phoneKey = 'helplineGeneral';
        }

        return [
            'email' => $this->translator->trans($emailKey, [], 'common'),
            'phone' => $this->translator->trans($phoneKey, [], 'common'),
        ];
    }

    /**
     * @return Email
     */
    public function createActivationEmail(User $user)
    {
        $area = $this->getUserArea($user);

        $parameters = array_merge($this->getContactParameters($user), [
            'activationLink' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'activate',
                'token' => $user->getRegistrationToken(),
            ]),
        ]);

        $email = (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translate('activation.fromName'))
            ->setToEmail($user->getEmail())
            ->setTemplate(self::ACTIVATION_TEMPLATE_ID)
            ->setParameters($parameters);

        return $email;
    }

    /**
     * @return Email
     */
    public function createInvitationEmail(User $user, ?string $deputyName = null)
    {
        $area = $this->getUserArea($user);

        $parameters = array_merge($this->getContactParameters($user), [
            'link' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'activate',
                'token' => $user->getRegistrationToken(),
            ]),
        ]);

        if (!is_null($deputyName)) {
            $parameters['deputyName'] = $deputyName;
        }

        $email = (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translate('activation.fromName'))
            ->setToEmail($user->getEmail())
            ->setTemplate($user->isLayDeputy() ? self::INVITATION_LAY_TEMPLATE_ID : self::INVITATION_ORG_TEMPLATE_ID)
            ->setParameters($parameters);

        return $email;
    }

    /**
     * Generates the recipient Role aspect of the context string. Most users use the 'default' recipient role.
     * This maps to the translation file.
     *
     * Called from BehatController to allow email-viewer to function
     *
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

    public function createResetPasswordEmail(User $user): Email
    {
        $area = $this->getUserArea($user);

        $notifyParams = array_merge($this->getContactParameters($user), [
            'resetLink' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'password-reset',
                'token' => $user->getRegistrationToken(),
            ]),
            'recreateLink' => $this->generateAbsoluteLink($area, 'password_forgotten'),
        ]);

        return (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translate('resetPassword.fromName'))
            ->setToEmail($user->getEmail())
            ->setTemplate(self::RESET_PASSWORD_TEMPLATE_ID)
            ->setParameters($notifyParams);
    }

    /**
     * Get user area depending on the role.
     *
     * @return string
     */
    private function getUserArea(User $user)
    {
        return $user->isDeputy() ? self::AREA_DEPUTY : self::AREA_ADMIN;
    }

    /**
     * @param array $response
     *
     * @return Email
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

        return (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translate('feedbackForm.fromName'))
            ->setToEmail($this->emailParams['feedback_send_to_address'])
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

        return (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translate('feedbackForm.fromName'))
            ->setToEmail($this->emailParams['feedback_send_to_address'])
            ->setTemplate(self::POST_SUBMISSION_FEEDBACK_TEMPLATE_ID)
            ->setParameters($notifyParams);
    }

    public function createUpdateClientDetailsEmail(Client $client): Email
    {
        $email = (new Email())
          ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
          ->setFromName($this->translator->trans('client.fromName', [], 'email'))
          ->setSubject($this->translator->trans('client.subject', [], 'email'))
          ->setToEmail($this->emailParams['update_send_to_address'])
          ->setTemplate(self::CLIENT_DETAILS_CHANGE_TEMPLATE_ID);

        $countryName = $this->intlService->getCountryNameByCountryCode($client->getCountry());

        $notifyParams = [
            'caseNumber' => $client->getCaseNumber(),
            'fullName' => $client->getFullName(),
            'address' => $client->getAddress(),
            'address2' => $client->getAddress2(),
            'address3' => $client->getAddress3(),
            'postcode' => $client->getPostcode(),
            'countryName' => $countryName,
            'phone' => $client->getPhone(),
        ];

        $email->setParameters($notifyParams);

        return $email;
    }

    public function createUpdateDeputyDetailsEmail(User $deputy): Email
    {
        $email = (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translator->trans('client.fromName', [], 'email'))
            ->setSubject($this->translator->trans('client.subject', [], 'email'))
            ->setToEmail($this->emailParams['update_send_to_address'])
            ->setTemplate(self::DEPUTY_DETAILS_CHANGE_TEMPLATE_ID);

        $countryName =
            $this->intlService->getCountryNameByCountryCode($deputy->getAddressCountry());

        $notifyParams = [
            'caseNumber' => $deputy->getFirstClient()->getCaseNumber(),
            'fullName' => $deputy->getFullName(),
            'address' => $deputy->getAddress1(),
            'address2' => null !== $deputy->getAddress2() ? $deputy->getAddress2() : 'Not provided',
            'address3' => null !== $deputy->getAddress3() ? $deputy->getAddress3() : 'Not provided',
            'postcode' => $deputy->getAddressPostcode(),
            'countryName' => $countryName,
            'phone' => $deputy->getPhoneMain(),
            'altPhoneNumber' => null !== $deputy->getPhoneAlternative() ? $deputy->getPhoneAlternative() : 'Not provided',
            'email' => $deputy->getEmail(),
        ];

        $email->setParameters($notifyParams);

        return $email;
    }

    /**
     * @return Email
     *
     * @throws \Exception
     */
    public function createReportSubmissionConfirmationEmail(User $user, EntityDir\ReportInterface $submittedReport, Report $newReport)
    {
        /** @var Email $email */
        $email = (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translator->trans('reportSubmissionConfirmation.fromName', [], 'email'))
            ->setToEmail($user->getEmail())
            ->setTemplate(self::REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID);

        /** @var \DateTime $dateSubmittableFrom */
        $dateSubmittableFrom = clone $newReport->getEndDate();
        $dateSubmittableFrom->add(new \DateInterval('P1D'));

        /** @var array $notifyParams */
        $notifyParams = [
            'clientFullname' => $submittedReport->getClient()->getFullname(),
            'deputyFullname' => $user->getFullName(),
            'orgIntro' => 'default' == self::getRecipientRole($user) ? '' : $this->buildOrgIntroText($submittedReport->getClient()),
            'startDate' => $submittedReport->getStartDate()->format(self::DATE_FORMAT),
            'endDate' => $submittedReport->getEndDate()->format(self::DATE_FORMAT),
            'homepageURL' => $this->generateAbsoluteLink(self::AREA_DEPUTY, 'homepage'),
            'newStartDate' => $newReport->getStartDate()->format(self::DATE_FORMAT),
            'newEndDate' => $newReport->getEndDate()->format(self::DATE_FORMAT),
            'EndDatePlus1' => $dateSubmittableFrom->format(self::DATE_FORMAT),
            'PFA' => '104' === substr($submittedReport->getType(), 0, 3) ? 'no' : 'yes',
            'lay' => $user->isLayDeputy() ? 'yes' : 'no',
        ];

        $email->setParameters($notifyParams);

        return $email;
    }

    private function buildOrgIntroText(Client $client): string
    {
        return $this->translator->trans(
            'caseDetails',
            ['%fullClientName%' => $client->getFullname(), '%caseNumber%' => $client->getCaseNumber()],
            'email-report-submission-confirm'
        );
    }

    /**
     * @return Email
     *
     * @throws \Exception
     */
    public function createNdrSubmissionConfirmationEmail(User $user, EntityDir\Ndr\Ndr $ndr, Report $report)
    {
        /** @var Email $email */
        $email = (new Email())
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
            'startDate' => $report->getStartDate()->format(self::DATE_FORMAT),
            'endDate' => $report->getEndDate()->format(self::DATE_FORMAT),
            'EndDatePlus1' => $dateSubmittableFrom->format(self::DATE_FORMAT),
            'PFA' => 'yes',
        ];

        $email->setParameters($notifyParams);

        return $email;
    }

    public function createProcessOrgCSVEmail(string $adminEmail, array $output): Email
    {
        $email = (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translator->trans('processOrgCSV.fromName', [], 'email'))
            ->setToEmail($adminEmail)
            ->setTemplate(self::PROCESS_ORG_CSV_TEMPLATE_ID);

        $isError = $output['errors']['count'] > 0 ? 'yes' : 'no';

        $notifyParams = [
            'addedClients' => $output['added']['clients'],
            'addedDeputies' => $output['added']['deputies'],
            'addedReports' => $output['added']['reports'],
            'addedOrganisations' => $output['added']['organisations'],
            'skipped' => $output['skipped'],
            'updatedClient' => $output['updated']['clients'],
            'updatedDeputies' => $output['updated']['deputies'],
            'updatedReports' => $output['updated']['reports'],
            'updatedOrganisations' => $output['updated']['organisations'],
            'isError' => $isError,
        ];

        $email->setParameters($notifyParams);

        return $email;
    }

    public function createProcessLayCSVEmail(string $adminEmail, array $output): Email
    {
        $email = (new Email())
            ->setFromEmailNotifyID(self::NOTIFY_FROM_EMAIL_ID)
            ->setFromName($this->translator->trans('processLayCSV.fromName', [], 'email'))
            ->setToEmail($adminEmail)
            ->setTemplate(self::PROCESS_LAY_CSV_TEMPLATE_ID);

        $errorCount = count($output['errors']);
        $isError = $errorCount > 0 ? 'yes' : 'no';

        $notifyParams = [
            'added' => $output['added'],
            'errors' => $errorCount,
            'skipped' => $output['skipped'],
            'isError' => $isError,
        ];

        $email->setParameters($notifyParams);

        return $email;
    }

    /**
     * @param string $key
     * @param array  $params
     *
     * @return string
     */
    private function translate($key, $params = [])
    {
        return $this->translator->trans($key, $params, 'email');
    }
}
