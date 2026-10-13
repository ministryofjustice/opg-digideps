<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Mailer;

use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Mail\NotifyMail;
use OPG\Digideps\Common\Mail\EmailTemplate;
use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Common\Validating\ValidationException;
use OPG\Digideps\Common\Account\AccountResponse;
use OPG\Digideps\Common\Account\Area;
use OPG\Digideps\Common\Account\PasswordResponse;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Service\IntlService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailFactory
{
    // Maintained in GOVUK Notify
    public const string ACTIVATION_TEMPLATE_ID = '07e7fdb3-ad81-4105-b6b6-c3854e0c6caa';
    public const string REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID = '2f8fff09-5a71-446a-a220-d8a3dc78fa42';
    public const string CLIENT_DETAILS_CHANGE_TEMPLATE_ID = '258aaf2d-076b-4b5c-a386-f3551c5f3945';
    public const string DEPUTY_DETAILS_CHANGE_TEMPLATE_ID = '6469b39b-6ace-4f93-9e80-6152627e0d36';
    public const string INVITATION_LAY_TEMPLATE_ID = 'b8afb0d0-c8e5-4191-bce7-74ba91c74cad';
    public const string INVITATION_ORG_TEMPLATE_ID = 'd410fce7-ce00-46eb-824d-82f998a437a4';
    public const string OLD_RESET_PASSWORD_TEMPLATE_ID = '827555cc-498a-43ef-957a-63fa387065e3';
    public const string PROCESS_ORG_CSV_TEMPLATE_ID = 'ce20ca97-a954-4d34-8a21-8b4f156188a8';
    public const string PROCESS_LAY_CSV_TEMPLATE_ID = '1e6fddc4-999d-4c44-8038-1853ea0e8511';
    public const string SET_PASSWORD_TEMPLATE_ID = '';
    public const string RESET_PASSWORD_TEMPLATE_ID = '';
    public const string CONFIRM_PASSWORD_TEMPLATE_ID = 'b2315b43-e0bf-45cf-876e-f2becea85374';

    public const string NOTIFY_FROM_EMAIL_ID = 'db930cb2-2153-4e2a-b3d0-06f7c7f92f37';

    public const string DATE_FORMAT = 'j F Y';

    public function __construct(
        protected TranslatorInterface $translator,
        protected RouterInterface $router,
        private readonly IntlService $intlService,
        /**
         * @var array<string> $emailParams
         */
        private readonly array $emailParams,
        private readonly array $baseURLs,
    ) {
    }

    /**
     * @param string $routeName must be in YML config under email.routes
     *
     * @return string calculated route
     * @throws \Exception
     */
    private function generateAbsoluteLink(Area $area, string $routeName, array $params = []): string
    {
        return match ($area) {
            Area::Deputy => $this->baseURLs['front'] . $this->router->generate($routeName, $params),
            Area::Admin => $this->baseURLs['admin'] . $this->router->generate($routeName, $params),
        };
    }

    private function getContactParameters(User $user): array
    {
        if ($user->isLayDeputy()) {
            $emailKey = 'layDeputySupportEmail';
        } elseif ($user->isDeputyPa()) {
            $emailKey = 'paSupportEmail';
        } elseif ($user->isDeputyProf()) {
            $emailKey = 'profSupportEmail';
        } else {
            $emailKey = 'generalSupportEmail';
        }

        return [
            'email' => $this->translator->trans($emailKey, [], 'common'),
            'phone' => $this->translator->trans('helpline', [], 'common'),
        ];
    }

    /**
     * @throws \Exception
     */
    public function createActivationEmail(User $user): NotifyMail
    {
        $area = $this->getUserArea($user);

        /**
         * @var array<string, string> $parameters
         */
        $parameters = array_merge($this->getContactParameters($user), [
            'activationLink' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'activate',
                'token' => $user->getRegistrationToken(),
            ]),
        ]);

        return new NotifyMail(
            new Email($user->getEmail()),
            EmailTemplate::Activation,
            $parameters
        );
    }

    /**
     * @throws \Exception
     */
    public function createInvitationEmail(User $user, ?string $deputyName = null): NotifyMail
    {
        $area = $this->getUserArea($user);

        /**
         * @var array<string, string> $parameters
         */
        $parameters = array_merge($this->getContactParameters($user), [
            'link' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'activate',
                'token' => $user->getRegistrationToken(),
            ]),
        ]);

        if (!is_null($deputyName)) {
            $parameters['deputyName'] = $deputyName;
        }

        return new NotifyMail(
            new Email($user->getEmail()),
            $user->isLayDeputy() ? EmailTemplate::InvitationLay : EmailTemplate::InvitationOrg,
            $parameters
        );
    }

    /**
     * Generates the recipient Role aspect of the context string. Most users use the 'default' recipient role.
     * This maps to the translation file.
     *
     * Called from BehatController to allow email-viewer to function
     */
    public static function getRecipientRole(User $user): string
    {
        return match ($user->getRoleName()) {
            User::ROLE_PA_NAMED, User::ROLE_PA_ADMIN, User::ROLE_PA_TEAM_MEMBER, User::ROLE_PROF_NAMED, User::ROLE_PROF_ADMIN, User::ROLE_PROF_TEAM_MEMBER => $user->getRoleName(),
            default => 'default',
        };
    }

    /**
     * @throws \Exception
     * @deprecated
     */
    public function createResetPasswordEmailOld(User $user): NotifyMail
    {
        $area = $this->getUserArea($user);

        /**
         * @var array<string, string> $notifyParams
         */
        $notifyParams = array_merge($this->getContactParameters($user), [
            'resetLink' => $this->generateAbsoluteLink($area, 'user_activate', [
                'action' => 'password-reset',
                'token' => $user->getRegistrationToken(),
            ]),
            'recreateLink' => $this->generateAbsoluteLink($area, 'password_forgotten'),
        ]);

        return new NotifyMail(
            new Email($user->getEmail()),
            EmailTemplate::LegacyResetPassword,
            $notifyParams
        );
    }

    /**
     * Get user area depending on the role.
     */
    private function getUserArea(User $user): Area
    {
        return $user->isDeputy() ? Area::Deputy : Area::Admin;
    }

    /**
     * @throws ValidationException
     */
    public function createUpdateClientDetailsEmail(Client $client): NotifyMail
    {
        $countryName = $this->intlService->getCountryNameByCountryCode($client->getCountry());

        /**
         * @var array<string, string> $notifyParams
         */
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

        return new NotifyMail(
            new Email($this->getDestinationEmailAddress($client)),
            EmailTemplate::ClientDetailsChange,
            $notifyParams
        );
    }

    public function createUpdateDeputyDetailsEmail(User $deputy): NotifyMail
    {
        $countryName = $this->intlService->getCountryNameByCountryCode($deputy->getAddressCountry());

        /**
         * @var array<string, string> $notifyParams
         */
        $notifyParams = [
            'caseNumber' => $deputy->getFirstClient()->getCaseNumber(),
            'fullName' => $deputy->getFullName(),
            'address' => $deputy->getAddress1(),
            'address2' => $deputy->getAddress2() !== null ? $deputy->getAddress2() : 'Not provided',
            'address3' => $deputy->getAddress3() !== null ? $deputy->getAddress3() : 'Not provided',
            'postcode' => $deputy->getAddressPostcode(),
            'countryName' => $countryName,
            'phone' => $deputy->getPhoneMain(),
            'altPhoneNumber' => $deputy->getPhoneAlternative() !== null ? $deputy->getPhoneAlternative() : 'Not provided',
            'email' => $deputy->getEmail(),
        ];

        return new NotifyMail(
            new Email($this->emailParams[MailerEmailAddress::UPDATE_ADDRESS_PFA_LAY->value]),
            EmailTemplate::DeputyDetailsChange,
            $notifyParams
        );
    }

    /**
     * @throws \Exception
     */
    public function createReportSubmissionConfirmationEmail(User $user, Report $submittedReport, Report $newReport): NotifyMail
    {
        /** @var \DateTime $dateSubmittableFrom */
        $dateSubmittableFrom = clone $newReport->getEndDate();
        $dateSubmittableFrom->add(new \DateInterval('P1D'));

        /**
         * @var array<string, string> $notifyParams
         */
        $notifyParams = [
            'clientFullname' => $submittedReport->getClient()->getFullname(),
            'deputyFullname' => $user->getFullName(),
            'orgIntro' => self::getRecipientRole($user) == 'default' ? '' : $this->buildOrgIntroText($submittedReport->getClient()),
            'startDate' => $submittedReport->getStartDate()?->format(self::DATE_FORMAT),
            'endDate' => $submittedReport->getEndDate()->format(self::DATE_FORMAT),
            'homepageURL' => $this->generateAbsoluteLink(Area::Deputy, 'homepage'),
            'newStartDate' => $newReport->getStartDate()?->format(self::DATE_FORMAT),
            'newEndDate' => $newReport->getEndDate()?->format(self::DATE_FORMAT),
            'EndDatePlus1' => $dateSubmittableFrom->format(self::DATE_FORMAT),
            'PFA' => str_starts_with($submittedReport->getType(), '104') ? 'no' : 'yes',
            'lay' => $user->isLayDeputy() ? 'yes' : 'no',
        ];

        return new NotifyMail(
            new Email($user->getEmail()),
            EmailTemplate::ReportSubmittedConfirmation,
            $notifyParams
        );
    }

    private function buildOrgIntroText(Client $client): string
    {
        return $this->translator->trans(
            'caseDetails',
            ['%fullClientName%' => $client->getFullname(), '%caseNumber%' => $client->getCaseNumber()],
            'email-report-submission-confirm'
        );
    }

    public function createProcessOrgCSVEmail(string $adminEmail, array $output): NotifyMail
    {
        $isError = $output['errors']['count'] > 0 ? 'yes' : 'no';

        /**
         * @var array<string, string> $notifyParams
         */
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

        return new NotifyMail(
            new Email($adminEmail),
            EmailTemplate::ProcessOrgCsv,
            $notifyParams
        );
    }

    public function createProcessLayCSVEmail(string $adminEmail, array $output): NotifyMail
    {
        $errorCount = count($output['errors']);
        $isError = $errorCount > 0 ? 'yes' : 'no';

        /**
         * @var array<string, string> $parameters
         */
        $parameters = [
            'added' => $output['added'],
            'errors' => $errorCount,
            'skipped' => $output['skipped'],
            'isError' => $isError,
        ];

        return new NotifyMail(
            new Email($adminEmail),
            EmailTemplate::ProcessLayCsv,
            $parameters
        );
    }

    public function createSetPasswordEmail(AccountResponse $response): NotifyMail
    {
        return new NotifyMail(
            $response->email,
            EmailTemplate::SetPassword,
            [
                ...$this->supportContacts($response->area, $response->deputyType),
                'tokenLink' => $this->generateAbsoluteLink(
                    $response->area,
                    'account_password_set',
                    [
                        'token' => "{$response->token}",
                        'key' => "{$response->validationKey}"
                    ]
                ),
                'recreateLink' => $this->generateAbsoluteLink($response->area, 'account_request'),
            ],
        );
    }

    public function createResetPasswordEmail(AccountResponse $response): NotifyMail
    {
        return new NotifyMail(
            $response->email,
            EmailTemplate::ResetPassword,
            [
                ...$this->supportContacts($response->area, $response->deputyType),
                'tokenLink' => $this->generateAbsoluteLink(
                    $response->area,
                    'account_reset',
                    [
                        'token' => "{$response->token}",
                        'key' => "{$response->validationKey}",
                    ],
                ),
                'recreateLink' => $this->generateAbsoluteLink($response->area, 'account_forgotten'),
            ],
        );
    }

    public function createConfirmPasswordEmail(PasswordResponse $response): NotifyMail
    {
        return new NotifyMail(
            $response->email,
            EmailTemplate::ConfirmPassword,
            $this->supportContacts($response->area, $response->deputyType)
        );
    }

    /**
     * @return array<string, string>
     */
    private function supportContacts(Area $area, DeputyType $deputyType): array
    {
        return [
            'supportEmail' => $this->translator->trans(match ($area) {
                Area::Deputy => match ($deputyType) {
                    DeputyType::LAY => 'layDeputySupportEmail',
                    DeputyType::PA => 'paSupportEmail',
                    DeputyType::PRO => 'profSupportEmail',
                },
                Area::Admin => 'generalSupportEmail',
            }, [], 'common'),
            'supportPhone' => $this->translator->trans('helpline', [], 'common'),
        ];
    }

    /**
     * @throws ValidationException
     */
    private function getDestinationEmailAddress(Client $client): string
    {
        $emailAddressKey = MailerEmailAddress::UPDATE_ADDRESS_PFA_LAY;
        if ($client->hasHwCourtOrder()) {
            $emailAddressKey = MailerEmailAddress::UPDATE_ADDRESS_HW;
        } elseif ($client->hasProDeputy()) {
            $emailAddressKey = MailerEmailAddress::UPDATE_ADDRESS_PFA_PRO;
        } elseif ($client->hasPaDeputy()) {
            $emailAddressKey = MailerEmailAddress::UPDATE_ADDRESS_PFA_PA;
        }

        $validatingArray = new ValidatingArray($this->emailParams);
        return $validatingArray->getStringOrThrow($emailAddressKey->value);
    }
}
