<?php

namespace AppBundle\Service;

use AppBundle\Controller\Report\ActionController;
use AppBundle\Controller\Report\AssetController;
use AppBundle\Controller\Report\BalanceController;
use AppBundle\Controller\Report\BankAccountController;
use AppBundle\Controller\Report\ContactController;
use AppBundle\Controller\Report\DebtController;
use AppBundle\Controller\Report\DecisionController;
use AppBundle\Controller\Report\DeputyExpenseController;
use AppBundle\Controller\Report\GiftController;
use AppBundle\Controller\Report\MoneyInController;
use AppBundle\Controller\Report\MoneyInShortController;
use AppBundle\Controller\Report\MoneyOutController;
use AppBundle\Controller\Report\MoneyOutShortController;
use AppBundle\Controller\Report\MoneyTransferController;
use AppBundle\Controller\Report\OtherInfoController;
use AppBundle\Controller\Report\PaFeeExpenseController;
use AppBundle\Controller\Report\ReportController;
use AppBundle\Controller\Report\VisitsCareController;
use AppBundle\Entity\Report\Report;
use Symfony\Component\HttpFoundation\RequestStack;

class ReportValidator
{
    private $validReportSections = [
        ActionController::class         => [Report::TYPE_102, Report::TYPE_103, Report::TYPE_104],
        AssetController::class          => [Report::TYPE_102, Report::TYPE_103],
        BalanceController::class        => [Report::TYPE_102, Report::TYPE_103],
        BankAccountController::class    => [Report::TYPE_102, Report::TYPE_103],
        ContactController::class        => [Report::TYPE_102, Report::TYPE_103, Report::TYPE_104],
        DebtController::class           => [Report::TYPE_102, Report::TYPE_103],
        DecisionController::class       => [Report::TYPE_102, Report::TYPE_103, Report::TYPE_104],
        DeputyExpenseController::class  => [Report::TYPE_102, Report::TYPE_103],
        GiftController::class           => [Report::TYPE_102, Report::TYPE_103],
        MoneyInController::class        => [Report::TYPE_102],
        MoneyOutController::class       => [Report::TYPE_102],
        MoneyInShortController::class   => [Report::TYPE_103],
        MoneyOutShortController::class  => [Report::TYPE_103],
        MoneyTransferController::class  => [Report::TYPE_102],
        OtherInfoController::class      => [Report::TYPE_102, Report::TYPE_103, Report::TYPE_104],
        ReportController::class         => [Report::TYPE_102, Report::TYPE_103, Report::TYPE_104],
        VisitsCareController::class     => [Report::TYPE_102, Report::TYPE_103, Report::TYPE_104],
        PaFeeExpenseController::class  => [Report::TYPE_102, Report::TYPE_103],
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
     * @param  Report $report
     * @return bool
     */
    public function isAllowedSection(Report $report)
    {
        $controllerParams = explode('::', $this->requestStack->getCurrentRequest()->get('_controller'));
        $currentRequestController = $controllerParams[0];

        if (!isset($this->validReportSections[$currentRequestController]) ||
            !in_array($report->getType(), $this->validReportSections[$currentRequestController])
        ) {
            return false;
        };

        return true;
    }
}
