<?php

namespace AppBundle\Twig;

use AppBundle\Entity\User;
use AppBundle\Service\ReportSectionsLinkService;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig_Environment;

class ComponentsExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ReportSectionsLinkService
     */
    private $reportSectionsLinkService;

    /**
     * ComponentsExtension constructor.
     * @param TranslatorInterface       $translator
     * @param ReportSectionsLinkService $reportSectionsLinkService
     */
    public function __construct(TranslatorInterface $translator, ReportSectionsLinkService $reportSectionsLinkService)
    {
        $this->translator = $translator;
        $this->reportSectionsLinkService = $reportSectionsLinkService;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('progress_bar_registration', [$this, 'progressBarRegistration'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('accordionLinks', [$this, 'renderAccordionLinks']),
            new \Twig_SimpleFunction('section_link_params', function ($report, $sectionId, $offset) {
                return $this->reportSectionsLinkService->getSectionParams($report, $sectionId, $offset);
            }),
            new TwigFunction('class_const', function($className, $constant) {
                return constant("$className::$constant");
            }),
        ];
    }

    public function getFilters()
    {
        return [
            'country_name' => new \Twig_SimpleFilter('country_name', function ($value) {
                return \Symfony\Component\Intl\Intl::getRegionBundle()->getCountryName($value);
            }),
            'last_loggedin_date_formatter' => new \Twig_SimpleFilter('last_loggedin_date_formatter', function ($value) {
                if ($value instanceof \DateTime) {
                    return $this->formatTimeDifference([
                        'from' => $value,
                        'to' => new \DateTime(),
                        'translationDomain' => 'common',
                        'translationPrefix' => 'lastLoggedIn.',
                        'defaultDateFormat' => 'd F Y',
                    ]);
                }
            }),
            'pad_day_month' => new \Twig_SimpleFilter('pad_day_month', function ($value) {
                if ($value && (int) $value >= 1 && (int) $value <= 9) {
                    return '0' . (int) $value;
                }

                return $value;
            }),
            // convert 'Very Random "string" !!" into "very-random-string"
            'behat_namify' => new \Twig_SimpleFilter('behat_namify', function ($string) {
                $string = preg_replace('/[^\s_\-a-zA-Z0-9]/u', '', $string); // remove unneeded chars
                $string = str_replace('_', ' ', $string);             // treat underscores as spaces
                $string = preg_replace('/^\s+|\s+$/', '', $string);   // trim leading/trailing spaces
                $string = preg_replace('/[-\s]+/', '-', $string);     // convert spaces to hyphens
                $string = strtolower($string);                        // convert to lowercase

                return $string;
            }),
            'money_format' => new \Twig_SimpleFilter('money_format', function ($string) {
                return number_format($string, 2, '.', ',');
            }),
            'class_name' => new \Twig_SimpleFilter('class_name', function ($object) {
                return is_object($object) ? get_class($object) : null;
            }),
            'lcfirst' => new \Twig_SimpleFilter('lcfirst', function ($string) {
                return lcfirst($string);
            }),
            'status_to_tag_css' => new \Twig_SimpleFilter('status_to_tag_css', function ($status) {
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
                    case 'not-matching':
                        return 'govuk-tag--red';

                    case 'done':
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

    /**
     * @param string $barName
     * @param int    $activeStepNumber
     */
    public function progressBarRegistration(Twig_Environment $env, User $user, $selectedStepId)
    {
        if ($user->isDeputyOrg() || in_array($user->getRoleName(), [User::ROLE_ADMIN, User::ROLE_AD, User::ROLE_SUPER_ADMIN])) {
            $availableStepIds = ['password', 'user_details'];
        } elseif ($user->isNdrEnabled() && !$user->getIsCoDeputy()) {
            $availableStepIds = ['password', 'user_details', 'client_details'];
        } elseif ($user->getIsCoDeputy()) {
            $availableStepIds = ['password', 'codep_verify'];
        } else {
            $availableStepIds = ['password', 'user_details', 'client_details', 'create_report'];
        }

        $progressSteps = [];
        $selectedStepNumber = array_search($selectedStepId, $availableStepIds);
        // set classes and labels from translation
        foreach ($availableStepIds as $currentStepNumber => $availableStepId) {
            $progressSteps[$availableStepId] = [
                'class' => (($selectedStepNumber == $currentStepNumber) ? ' opg-progress-bar__item--active ' : '')
                    . (($currentStepNumber < $selectedStepNumber) ? ' opg-progress-bar__item--completed ' : '')
                    . (($currentStepNumber == $selectedStepNumber - 1) ? ' opg-progress-bar__item--previous ' : ''),
            ];
        }

        echo $env->render('AppBundle:Components/Navigation:_progress-indicator.html.twig', [
            'progressSteps' => $progressSteps,
        ]);
    }

    public function getName()
    {
        return 'components_extension';
    }
}
