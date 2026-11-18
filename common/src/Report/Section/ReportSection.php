<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section;

enum ReportSection: string
{
    case ACTIONS = 'actions';
    case ASSETS = 'assets';
    case BALANCE = 'balance';
    case BANK_ACCOUNTS = 'bankAccounts';
    case CLIENT_BENEFITS_CHECK = 'clientBenefitsCheck';
    case CONTACTS = 'contacts';
    case DEBTS = 'debts';
    case DECISIONS = 'decisions';
    case DEPUTY_EXPENSES = 'deputyExpenses';
    case DOCUMENTS = 'documents';
    case GIFTS = 'gifts';
    case LIFESTYLE = 'lifestyle';
    case MONEY_IN = 'moneyIn';
    case MONEY_IN_SHORT = 'moneyInShort';
    case MONEY_OUT = 'moneyOut';
    case MONEY_OUT_SHORT = 'moneyOutShort';
    case MONEY_TRANSFERS = 'moneyTransfers';
    case OTHER_INFO = 'otherInfo';
    case PA_DEPUTY_EXPENSES = 'paDeputyExpenses'; // 106, AKA Fee and expenses
    case PROF_CURRENT_FEES = 'profCurrentFees';
    case PROF_DEPUTY_COSTS = 'profDeputyCosts';
    case PROF_DEPUTY_COSTS_ESTIMATE = 'profDeputyCostsEstimate';
    case VISITS_CARE = 'visitsCare';

    // work-around for the one case where the value of the enum cannot be snake-cased to get the route name
    public function getValueForRouting(): string
    {
        if ($this === self::PA_DEPUTY_EXPENSES) {
            return 'paFeeExpense';
        }

        return $this->value;
    }
}
