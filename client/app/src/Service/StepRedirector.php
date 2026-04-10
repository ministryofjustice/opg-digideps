<?php

namespace OPG\Digideps\Frontend\Service;

use Symfony\Component\Routing\RouterInterface;

class StepRedirector
{
    private string $step1BackLink;
    private string $routeSummary;
    private string $routeStep;
    private array $routeBaseParams;
    private ?string $fromPage;
    private int $currentStep;
    private int $totalSteps;
    private array $stepUrlAdditionalParams = [];

    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function setRoutes(string $step1BackLink, string $routeStep, string $routeSummary): static
    {
        $this->step1BackLink = $step1BackLink;
        $this->routeStep = $routeStep;
        $this->routeSummary = $routeSummary;

        return $this;
    }

    public function setFromPage(?string $fromPage): static
    {
        $this->fromPage = $fromPage;

        return $this;
    }

    public function setCurrentStep(mixed $currentStep): static
    {
        $this->currentStep = (int) $currentStep;

        return $this;
    }

    public function setTotalSteps(mixed $totalSteps): static
    {
        $this->totalSteps = (int) $totalSteps;

        return $this;
    }

    public function setRouteBaseParams(array $routeBaseParams): static
    {
        $this->routeBaseParams = $routeBaseParams;

        return $this;
    }

    public function setStepUrlAdditionalParams(array $stepUrlAdditionalParams): static
    {
        $this->stepUrlAdditionalParams = $stepUrlAdditionalParams;

        return $this;
    }

    public function getRedirectLinkAfterSaving(array $extraParams = []): string
    {
        // return to summary if coming from there, or it's the last step
        if ('summary' === $this->fromPage) {
            return $this->generateUrl($this->routeSummary, ['stepEdited' => $this->currentStep]);
        }

        if ($this->currentStep === $this->totalSteps) {
            return $this->generateUrl($this->routeSummary, ['from' => 'last-step'] + $extraParams);
        }

        return $this->generateUrl(
            $this->routeStep,
            ['step' => $this->currentStep + 1] + $this->stepUrlAdditionalParams
        );
    }

    public function getBackLink(): string
    {
        if ('summary' === $this->fromPage) {
            return $this->generateUrl($this->routeSummary, ['from' => 'skip-step']);
        }

        if (1 === $this->currentStep) {
            return $this->generateUrl($this->step1BackLink);
        }

        return $this->generateUrl(
            $this->routeStep,
            ['step' => $this->currentStep - 1] + $this->stepUrlAdditionalParams
        );
    }

    public function getSkipLink(): ?string
    {
        if (!empty($this->fromPage) || 1 === $this->totalSteps) {
            return null;
        }
        if ($this->currentStep == $this->totalSteps) {
            return $this->generateUrl($this->routeSummary, ['from' => 'skip-step']);
        }

        return $this->generateUrl($this->routeStep, ['step' => $this->currentStep + 1]);
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->router->generate($route, $this->routeBaseParams + $params);
    }
}
