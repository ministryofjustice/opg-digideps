<?php

namespace AppBundle\Service;

use AppBundle\Controller\Report\ActionController;
use AppBundle\Controller\Report\BalanceController;
use AppBundle\Controller\Report\ContactController;
use AppBundle\Controller\Report\DecisionController;
use AppBundle\Controller\Report\OtherInfoController;
use AppBundle\Controller\Report\VisitsCareController;
use AppBundle\Controller\Report\ReportController;
use AppBundle\Entity\Report\Report;
use Symfony\Component\HttpFoundation\RequestStack;

class ReportValidator
{
    private $validReportSections = [
        'all' => [
            ActionController::class,
            BalanceController::class,
            ReportController::class,
        ],
        104 => [
            ContactController::class,
            DecisionController::class,
            OtherInfoController::class,
            VisitsCareController::class
        ]
    ];

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * ReportValidator constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Validates whether a controller should be allowed to execute it's actions for this report. Used to protect
     * sections from being accessed when no relevance to the report type.
     *
     * @param Report $report
     * @return bool
     */
    public function isAllowedSection(Report $report)
    {
        if (!array_key_exists($report->getType(), $this->validReportSections)) {
            return true;
        }

        $controllerParams = explode('::',$this->requestStack->getCurrentRequest()->get('_controller'));
        $currentRequestController = $controllerParams[0];

        if(
            !in_array($currentRequestController, $this->validReportSections['all']) &&
            !in_array($currentRequestController, $this->validReportSections[$report->getType()])
        ) {
            echo $currentRequestController;
            return false;
        };

        return true;
    }
}
