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

    /**
     * @var string
     */
    private $routeStartPage;

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
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->stepUrlAdditionalParams = [];
    }


    /**
     * @param mixed $routePrefix
     * @return StepRedirector
     */
    public function setRoutePrefix($routePrefix)
    {
        $this->routeStartPage = rtrim($routePrefix, '_');
        $this->routeSummary = rtrim($routePrefix, '_') . '_summary';
        $this->routeStep = rtrim($routePrefix, '_') . '_step';

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
        $this->currentStep = (int)$currentStep;
        return $this;
    }

    /**
     * @param mixed $totalSteps
     */
    public function setTotalSteps($totalSteps)
    {
        $this->totalSteps = (int)$totalSteps;
        return $this;
    }

    /**
     * @param array $routeBaseParams
     * @return StepRedirector
     */
    public function setRouteBaseParams(array $routeBaseParams)
    {
        $this->routeBaseParams = $routeBaseParams;
        return $this;
    }


    /**
     * @param mixed $stepUrlAdditionalParams
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
            return $this->generateUrl($this->routeSummary);
        } else if ($this->currentStep == 1) {
            return $this->generateUrl($this->routeStartPage);
        }

        return $this->generateUrl($this->routeStep, [
            'step' => $this->currentStep - 1
            ] + $this->stepUrlAdditionalParams);
    }

    public function getSkipLink()
    {
        if (!empty($this->fromPage)) {
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