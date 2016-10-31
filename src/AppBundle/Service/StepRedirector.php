<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 28/10/2016
 * Time: 15:50
 */

namespace AppBundle\Service;

use Symfony\Component\Routing\RouterInterface;

class StepRedirector
{
    /**
     * @var RouterInterface
     */
    protected $router;

    private $routeSummaryOverview;
    private $routeSummaryCheck;
    private $routeStep;
    private $fromPage;
    private $currentStep;
    private $totalSteps;
    private $reportId;

    /**
     * StepRedirector constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }


    /**
     * @param mixed $routePrefix
     * @return StepRedirector
     */
    public function setRoutePrefix($routePrefix)
    {
        $this->routeSummaryCheck = $routePrefix . 'check';
        $this->routeSummaryOverview = $routePrefix . 'overview';
        $this->routeStep = $routePrefix . 'step';

        return $this;
    }


    /**
     * @param mixed $this ->fromPage
     * @return StepRedirector
     */
    public function setFromPage($fromPage)
    {
        $this->fromPage = $fromPage;
        return $this;
    }


    /**
     * @param mixed $currentStep
     * @return StepRedirector
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;
        return $this;
    }

    /**
     * @param mixed $totalSteps
     */
    public function setTotalSteps($totalSteps)
    {
        $this->totalSteps = $totalSteps;
        return $this;
    }

    /**
     * @param mixed $reportId
     */
    public function setReportId($reportId)
    {
        $this->reportId = $reportId;
        return $this;
    }


    public function getRedirectLinkAfterSaving()
    {
        // return to summary if coming from there, or it's the last step
        if ($this->fromPage == 'overview') {
            return $this->router->generate('visits_care_summary_overview', ['reportId' => $this->reportId, 'stepEdited' => $this->currentStep]);
        }
        if ($this->fromPage == 'check') {
            return $this->router->generate('visits_care_summary_check', ['reportId' => $this->reportId, 'stepEdited' => $this->currentStep]);
        }
        if ($this->currentStep == $this->totalSteps) {
            return $this->router->generate('visits_care_summary_check', ['reportId' => $this->reportId]);
        }

        return $this->router->generate('visits_care_step', ['reportId' => $this->reportId, 'step' => $this->currentStep + 1]);
    }

    public function getBackLink()
    {
        $backLink = null;
        if ($this->fromPage === 'overview') {
            return $this->router->generate('visits_care_summary_overview', ['reportId' => $this->reportId]);
        } else if ($this->fromPage === 'check') {
            return $this->router->generate('visits_care_summary_check', ['reportId' => $this->reportId]);
        } else if ($this->currentStep == 1) {
            return $this->router->generate('visits_care', ['reportId' => $this->reportId]);
        }

        return $this->router->generate('visits_care_step', ['reportId' => $this->reportId, 'step' => $this->currentStep - 1]);
    }

    public function getSkipLink()
    {
        if (!empty($this->fromPage)) {
            return null;
        }
        if ($this->currentStep == $this->totalSteps) {
            return $this->router->generate('visits_care_summary_check', ['reportId' => $this->reportId, 'from' => 'skip-step']);
        }
        return $this->router->generate('visits_care_step', ['reportId' => $this->reportId, 'step' => $this->currentStep + 1]);
    }


}