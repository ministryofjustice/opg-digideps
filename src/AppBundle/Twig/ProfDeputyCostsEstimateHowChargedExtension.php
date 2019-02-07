<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Translation\TranslatorInterface;

class ProfDeputyCostsEstimateHowChargedExtension extends \Twig_Extension
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array|\Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('howCharged', [$this, 'renderHowCharged']),
        ];
    }

    /**
     * @param $howCharged
     * @return string
     */
    public function renderHowCharged($howCharged)
    {
        switch ($howCharged) {
            case Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED:
                return $this->translator->trans('howCharged.form.options.fixed', [], 'report-prof-deputy-costs-estimate');
            case Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_ASSESSED:
                return $this->translator->trans('howCharged.form.options.assessed', [], 'report-prof-deputy-costs-estimate');
            case Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_BOTH:
                return $this->translator->trans('howCharged.form.options.both', [], 'report-prof-deputy-costs-estimate');
            default:
                throw new \InvalidArgumentException(sprintf("Unexpected argument: '%s'", $howCharged));
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'prof_deputy_costs_estimate_how_charged';
    }
}
