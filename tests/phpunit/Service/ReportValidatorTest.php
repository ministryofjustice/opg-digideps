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
use Mockery\Adapter\Phpunit\MockeryTestCase;
use MockeryStub as m;
use Symfony\Component\HttpFoundation\RequestStack;

class ReportValidatorTest extends MockeryTestCase
{
    /**
     * @var ReportValidator
     */
    protected $sut;

    /**
     * @dataProvider sectionProvidor
     */
    public function testIsAllowedSection($reportType, $controllerName, $expected)
    {
        $mockRequestStack = m::mock(RequestStack::class);
        $mockRequestStack->shouldReceive('getCurrentRequest')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('_controller')
            ->andReturn($controllerName . '::someAction');

        $mockReport = new Report();
        $mockReport->setType($reportType);

        $this->sut = new ReportValidator($mockRequestStack);

        $this->assertEquals($expected, $this->sut->isAllowedSection($mockReport));
    }

    public function sectionProvidor()
    {
        return [
                // REPORT TYPE 102
                [Report::TYPE_102, ActionController::class, true],
                [Report::TYPE_102, AssetController::class, true],
                [Report::TYPE_102, BalanceController::class, true],
                [Report::TYPE_102, BankAccountController::class, true],
                [Report::TYPE_102, ContactController::class, true],
                [Report::TYPE_102, DebtController::class, true],
                [Report::TYPE_102, DecisionController::class, true],
                [Report::TYPE_102, DeputyExpenseController::class, true],
                [Report::TYPE_102, PaFeeExpenseController::class, true],
                [Report::TYPE_102, GiftController::class, true],
                [Report::TYPE_102, MoneyInController::class, true],
                [Report::TYPE_102, MoneyInShortController::class, false],
                [Report::TYPE_102, MoneyOutController::class, true],
                [Report::TYPE_102, MoneyOutShortController::class, false],
                [Report::TYPE_102, MoneyTransferController::class, true],
                [Report::TYPE_102, OtherInfoController::class, true],
                [Report::TYPE_102, ReportController::class, true],
                [Report::TYPE_102, VisitsCareController::class, true],

                // REPORT TYPE 103
                [Report::TYPE_103, ActionController::class, true],
                [Report::TYPE_103, AssetController::class, true],
                [Report::TYPE_103, BalanceController::class, true],
                [Report::TYPE_103, BankAccountController::class, true],
                [Report::TYPE_103, ContactController::class, true],
                [Report::TYPE_103, DebtController::class, true],
                [Report::TYPE_103, DecisionController::class, true],
                [Report::TYPE_103, DeputyExpenseController::class, true],
                [Report::TYPE_103, PaFeeExpenseController::class, true],
                [Report::TYPE_103, GiftController::class, true],
                [Report::TYPE_103, MoneyInController::class, false],
                [Report::TYPE_103, MoneyInShortController::class, true],
                [Report::TYPE_103, MoneyOutController::class, false],
                [Report::TYPE_103, MoneyOutShortController::class, true],
                [Report::TYPE_103, MoneyTransferController::class, false],
                [Report::TYPE_103, OtherInfoController::class, true],
                [Report::TYPE_103, ReportController::class, true],
                [Report::TYPE_103, VisitsCareController::class, true],

                // REPORT TYPE 104
                [Report::TYPE_104, ActionController::class, true],
                [Report::TYPE_104, AssetController::class, false],
                [Report::TYPE_104, BalanceController::class, false],
                [Report::TYPE_104, BankAccountController::class, false],
                [Report::TYPE_104, ContactController::class, true],
                [Report::TYPE_104, DebtController::class, false],
                [Report::TYPE_104, DecisionController::class, true],
                [Report::TYPE_104, DeputyExpenseController::class, false],
                [Report::TYPE_104, GiftController::class, false],
                [Report::TYPE_104, MoneyInController::class, false],
                [Report::TYPE_104, MoneyInShortController::class, false],
                [Report::TYPE_104, MoneyOutController::class, false],
                [Report::TYPE_104, MoneyOutShortController::class, false],
                [Report::TYPE_104, MoneyTransferController::class, false],
                [Report::TYPE_104, OtherInfoController::class, true],
                [Report::TYPE_104, ReportController::class, true],
                [Report::TYPE_104, VisitsCareController::class, true]


        ];
    }
}
