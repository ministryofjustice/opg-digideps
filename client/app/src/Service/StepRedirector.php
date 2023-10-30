<?php

/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 28/10/2016
 * Time: 15:50.
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
    private $routeMoneyInExists;

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
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->stepUrlAdditionalParams = [];
    }

    /**
     * @return $this
     */
    public function setRoutes($step1BackLink, $routeMoneyInExists, $routeStep, $routeSummary)
    {
        $this->step1BackLink = $step1BackLink;
        $this->routeMoneyInExists = $routeMoneyInExists;
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

    public function getRedirectLinkAfterSaving(array $extraParams = [])
    {
        // return to summary if coming from there, or it's the last step
        if ('summary' === $this->fromPage) {
            return $this->generateUrl($this->routeSummary, [
                'stepEdited' => $this->currentStep,
            ]);
        }
        if ($this->currentStep === $this->totalSteps) {
            return $this->generateUrl($this->routeSummary, ['from' => 'last-step'] + $extraParams);
        }

        return $this->generateUrl($this->routeStep, [
                'step' => $this->currentStep + 1,
            ] + $this->stepUrlAdditionalParams);
    }

    public function getBackLink()
    {
        if ('summary' === $this->fromPage) {
            return $this->generateUrl($this->routeSummary, ['from' => 'skip-step']);
        } elseif (1 == $this->currentStep) {
            return $this->generateUrl($this->routeMoneyInExists);
        }

        return $this->generateUrl($this->routeStep, [
            'step' => $this->currentStep - 1,
            ] + $this->stepUrlAdditionalParams);
    }

    public function getSkipLink()
    {
        if (!empty($this->fromPage) || 1 == $this->totalSteps) {
            return null;
        }
        if ($this->currentStep == $this->totalSteps) {
            return $this->generateUrl($this->routeSummary, [
                'from' => 'skip-step',
            ]);
        }

        return $this->generateUrl($this->routeStep, [
            'step' => $this->currentStep + 1,
        ]);
    }

    private function generateUrl($route, array $params = [])
    {
        return $this->router->generate($route, $this->routeBaseParams + $params);
    }
}
