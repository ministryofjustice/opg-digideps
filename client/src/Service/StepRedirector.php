<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 28/10/2016
 * Time: 15:50
 */

namespace App\Service;

use Symfony\Component\Routing\RouterInterface;

class StepRedirector
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    private $step1BackLink;

    /**
     * @var string
     */
    private $routeSummary;

    /**
     * @var string
     */
    private $routeStep;

    /**
     * @var array
     */
    private $routeBaseParams;

    /**
     * @var string
     */
    private $fromPage;
    /**
     * @var string
     */
    private $currentStep;
    /**
     * @var string
     */
    private $totalSteps;

    /**
     * @var array
     */
    private $stepUrlAdditionalParams;

    /**
     * StepRedirector constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->stepUrlAdditionalParams = [];
    }

    /**
     * @param $step1BackLink
     * @param $routeStep
     * @param $routeSummary
     *
     * @return $this
     */
    public function setRoutes($step1BackLink, $routeStep, $routeSummary)
    {
        $this->step1BackLink = $step1BackLink;
        $this->routeStep = $routeStep;
        $this->routeSummary = $routeSummary;

        return $this;
    }

    /**
     * @param mixed $this ->fromPage
     *
     * @return StepRedirector
     */
    public function setFromPage($fromPage)
    {
        $this->fromPage = $fromPage;
        return $this;
    }

    /**
     * @param mixed $currentStep
     *
     * @return StepRedirector
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = (int) $currentStep;
        return $this;
    }

    /**
     * @param mixed $totalSteps
     */
    public function setTotalSteps($totalSteps)
    {
        $this->totalSteps = (int) $totalSteps;
        return $this;
    }

    /**
     * @param array $routeBaseParams
     *
     * @return StepRedirector
     */
    public function setRouteBaseParams(array $routeBaseParams)
    {
        $this->routeBaseParams = $routeBaseParams;
        return $this;
    }

    /**
     * @param mixed $stepUrlAdditionalParams
     *
     * @return StepRedirector
     */
    public function setStepUrlAdditionalParams(array $stepUrlAdditionalParams)
    {
        $this->stepUrlAdditionalParams = $stepUrlAdditionalParams;
        return $this;
    }

    public function getRedirectLinkAfterSaving()
    {
        // return to summary if coming from there, or it's the last step
        if ($this->fromPage === 'summary') {
            return $this->generateUrl($this->routeSummary, [
                'stepEdited' => $this->currentStep
            ]);
        }
        if ($this->currentStep === $this->totalSteps) {
            return $this->generateUrl($this->routeSummary, ['from'=>'last-step']);
        }

        return $this->generateUrl($this->routeStep, [
                'step' => $this->currentStep + 1,
            ] + $this->stepUrlAdditionalParams);
    }

    public function getBackLink()
    {
        if ($this->fromPage === 'summary') {
            return $this->generateUrl($this->routeSummary, ['from'=>'skip-step']);
        } elseif ($this->currentStep == 1) {
            return $this->generateUrl($this->step1BackLink);
        }

        return $this->generateUrl($this->routeStep, [
            'step' => $this->currentStep - 1
            ] + $this->stepUrlAdditionalParams);
    }

    public function getSkipLink()
    {
        if (!empty($this->fromPage) || $this->totalSteps == 1) {
            return null;
        }
        if ($this->currentStep == $this->totalSteps) {
            return $this->generateUrl($this->routeSummary, [
                'from' => 'skip-step'
            ]);
        }

        return $this->generateUrl($this->routeStep, [
            'step' => $this->currentStep + 1
        ]);
    }

    private function generateUrl($route, array $params = [])
    {
        return $this->router->generate($route, $this->routeBaseParams + $params);
    }
}
