<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\ReportSectionsLinkService;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ComponentsExtension extends AbstractExtension
{
    private TranslatorInterface $translator;
    private ReportSectionsLinkService $reportSectionsLinkService;

    /**
     * ComponentsExtension constructor.
     */
    public function __construct(
        TranslatorInterface $translator,
        ReportSectionsLinkService $reportSectionsLinkService,
    ) {
        $this->translator = $translator;
        $this->reportSectionsLinkService = $reportSectionsLinkService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('progress_bar_registration', [$this, 'progressBarRegistration'], ['needs_environment' => true]),
            new TwigFunction('progress_bar_report_submission', [$this, 'progressBarReportSubmission'], ['needs_environment' => true]),
            new TwigFunction('accordionLinks', [$this, 'renderAccordionLinks']),
            new TwigFunction('section_link_params', function ($report, $sectionId, $offset) {
                return $this->reportSectionsLinkService->getSectionParams($report, $sectionId, $offset);
            }),
            new TwigFunction('class_const', function ($className, $constant) {
                return constant("$className::$constant");
            }),
        ];
    }

    public function getFilters(): array
    {
        return [
            'country_name' => new TwigFilter('country_name', function ($value) {
                return Countries::getName($value);
            }),
            'last_loggedin_date_formatter' => new TwigFilter('last_loggedin_date_formatter', function ($value) {
                if ($value instanceof \DateTime) {
                    return $this->formatTimeDifference([
                        'from' => $value,
                        'to' => new \DateTime(),
                        'translationDomain' => 'common',
                        'translationPrefix' => 'lastLoggedIn.',
                        'defaultDateFormat' => 'd F Y',
                    ]);
                }

                return '';
            }),
            'pad_day_month' => new TwigFilter('pad_day_month', function ($value) {
                if ($value && (int) $value >= 1 && (int) $value <= 9) {
                    return '0' . (int) $value;
                }

                return $value;
            }),
            // convert 'Very Random "string" !!' into 'very-random-string'
            'behat_namify' => new TwigFilter('behat_namify', function ($string) {
                $string = preg_replace('/[^\s_\-a-zA-Z0-9]/u', '', $string); // remove unnecessary chars
                $string = str_replace('_', ' ', $string);              // treat underscores as spaces
                $string = trim($string);                               // trim leading/trailing spaces
                $string = preg_replace('/[-\s]+/', '-', $string);      // convert spaces to hyphens

                return is_null($string) ? '' : strtolower($string);
            }),
            'money_format' => new TwigFilter('money_format', function ($string) {
                return number_format($string, 2);
            }),
            'class_name' => new TwigFilter('class_name', function ($object) {
                return is_object($object) ? get_class($object) : null;
            }),
            'lcfirst' => new TwigFilter('lcfirst', function ($string) {
                if (is_null($string)) {
                    return null;
                }

                return lcfirst($string);
            }),
            'status_to_tag_css' => new TwigFilter('status_to_tag_css', function ($status) {
                switch ($status) {
                    case 'notStarted':
                    case 'not-started':
                        return 'govuk-tag--grey';

                    case 'notFinished':
                    case 'active':
                    case 'incomplete':
                        return 'govuk-tag--yellow';

                    case 'needs-attention':
                    case 'unsubmitted':
                        return 'govuk-tag--red';

                    case 'not-matching':
                    case 'explained':
                        return 'govuk-tag--blue';

                    case 'done':
                    case 'low-assets-done':
                    case 'submitted':
                    case 'readyToSubmit':
                        return 'govuk-tag--green';

                    default:
                        return '';
                }
            }),
        ];
    }

    /**
     * @param \DateTime from
     * @param \DateTime to
     * @param string translationPrefix
     * @param string defaultDateFormat e.g. d F Y
     * @param string translationDomain
     *
     * @return string formatted interval
     */
    public function formatTimeDifference(array $options)
    {
        $from = $options['from'];
        $to = $options['to'];
        $translationPrefix = $options['translationPrefix'];
        $defaultDateFormat = $options['defaultDateFormat'];
        $translationDomain = $options['translationDomain'];

        $secondsDiff = $to->getTimestamp() - $from->getTimestamp();

        if ($secondsDiff < 60) {
            return $this->translator->trans($translationPrefix . 'lessThenAMinuteAgo', [], $translationDomain);
        }

        if ($secondsDiff < 3600) {
            $minutes = (int) round($secondsDiff / 60, 0);

            return $this->translator->transChoice($translationPrefix . 'minutesAgo', $minutes, ['%count%' => $minutes], $translationDomain);
        }

        if ($secondsDiff < 86400) {
            $hours = (int) round($secondsDiff / 3600, 0);

            return $this->translator->transChoice($translationPrefix . 'hoursAgo', $hours, ['%count%' => $hours], $translationDomain);
        }

        return $this->translator->trans($translationPrefix . 'exactDate', ['%date%' => $from->format($defaultDateFormat)], $translationDomain);
    }

    /**
     * @param array $options keys: clickedPanel, allOpenHref, allClosedHref, firstPanelHref, secondPanelHref
     *
     * @return array [ first => [open=>boolean, href=>], second => [open=>boolean, href=>] ]
     */
    public function renderAccordionLinks(array $options)
    {
        $clickedPanel = $options['clickedPanel'];
        $bothOpenHref = $options['bothOpenHref'];
        $allClosedHref = $options['allClosedHref'];
        $firstPanelHref = $options['firstPanelHref'];
        $secondPanelHref = $options['secondPanelHref'];
        $onlyOneATime = $options['onlyOneATime'];

        // default: closed
        $ret = [
            'first' => [
                'open' => false,
                'href' => $firstPanelHref,
            ],
            'second' => [
                'open' => false,
                'href' => $secondPanelHref,
            ],
        ];

        switch ($clickedPanel) {
            case $firstPanelHref:
                $ret['first']['open'] = true;
                $ret['first']['href'] = $allClosedHref;
                $ret['second']['href'] = $onlyOneATime ? $secondPanelHref : $bothOpenHref;
                break;

            case $secondPanelHref:
                $ret['second']['open'] = true;
                $ret['first']['href'] = $onlyOneATime ? $firstPanelHref : $bothOpenHref;
                $ret['second']['href'] = $allClosedHref;
                break;

            case $bothOpenHref:
                $ret['first']['open'] = true;
                $ret['second']['open'] = true;
                $ret['first']['href'] = $secondPanelHref;
                $ret['second']['href'] = $firstPanelHref;
                break;
        }

        return $ret;
    }

    public function progressBarRegistration(Environment $env, User $user, $selectedStepId): void
    {
        if ($user->isDeputyOrg() || in_array($user->getRoleName(), [User::ROLE_ADMIN, User::ROLE_AD, User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_MANAGER])) {
            $availableStepIds = ['password', 'user_details'];
        } elseif ($user->getIsCoDeputy() || User::CO_DEPUTY_INVITE === $user->getRegistrationRoute()) {
            $availableStepIds = ['password', 'codep_verify'];
        } elseif ($user->isNdrEnabled()) {
            $availableStepIds = ['password', 'user_details', 'client_details'];
        } else {
            $availableStepIds = ['password', 'user_details', 'client_details'];
        }

        $progressSteps = $this->getProgressSteps($selectedStepId, $availableStepIds);

        echo $env->render('@App/Components/Navigation/_progress-indicator.html.twig', [
            'progressSteps' => $progressSteps,
            'progressBar' => 'registrationProgressBar',
        ]);
    }

    public function progressBarReportSubmission(Environment $env, string $selectedStepId): void
    {
        $availableStepIds = ['review_report', 'report_confirm_details', 'report_declaration'];

        $progressSteps = $this->getProgressSteps($selectedStepId, $availableStepIds);

        echo $env->render('@App/Components/Navigation/_progress-indicator.html.twig', [
            'progressSteps' => $progressSteps,
            'progressBar' => 'reportSubmissionProgressBar',
        ]);
    }

    public function getName()
    {
        return 'components_extension';
    }

    // $currentStepIdentifier: the identifier of the current step, e.g. 'user_details'; should appear in
    // $availableStepIdentifiers
    //
    // $availableStepIdentifiers: array of step identifiers in the order they appear in the progress bar,
    // e.g. ['password', 'user_details', 'client_details']
    private function getProgressSteps(string $currentStepIdentifier, array $availableStepIdentifiers): array
    {
        // get the number of the step we're currently at in the process
        $currentStepNumber = array_search($currentStepIdentifier, $availableStepIdentifiers);

        if (!is_numeric($currentStepNumber)) {
            return [];
        }

        $progressSteps = [];

        foreach ($availableStepIdentifiers as $availableStepNumber => $availableStepIdentifier) {
            $stepStatus = 'incomplete';

            if ($availableStepNumber === $currentStepNumber) {
                $stepStatus = 'active';
            } elseif ($availableStepNumber === $currentStepNumber - 1) {
                // this is required to put the correct ending chevron on the progress bar segment before the active one
                $stepStatus = 'previous';
            } elseif ($availableStepNumber < $currentStepNumber) {
                $stepStatus = 'completed';
            }

            $progressSteps[$availableStepIdentifier] = [
                'stepStatus' => $stepStatus,
            ];
        }

        return $progressSteps;
    }
}
