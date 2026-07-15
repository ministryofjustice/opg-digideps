<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section;

enum ReportSection: string
{
    case DECISIONS = 'decisions';
    case CONTACTS = 'contacts';
    case VISITS_CARE = 'visitsCare';
    case LIFESTYLE = 'lifestyle';
    case CLIENT_BENEFITS_CHECK = 'clientBenefitsCheck';
    case BANK_ACCOUNTS = 'bankAccounts';
    case GIFTS = 'gifts';
    case MONEY_TRANSFERS = 'moneyTransfers';
    case MONEY_IN = 'moneyIn';
    case MONEY_OUT = 'moneyOut';
    case BALANCE = 'balance';
    case MONEY_IN_SHORT = 'moneyInShort';
    case MONEY_OUT_SHORT = 'moneyOutShort';
    case ASSETS = 'assets';
    case DEBTS = 'debts';
    case DEPUTY_EXPENSES = 'deputyExpenses';
    case PA_DEPUTY_EXPENSES = 'paDeputyExpenses'; // 106, AKA Fee and expenses
    case PROF_DEPUTY_COSTS = 'profDeputyCosts';
    case PROF_DEPUTY_COSTS_ESTIMATE = 'profDeputyCostsEstimate';
    case ACTIONS = 'actions';
    case OTHER_INFO = 'otherInfo';
    case DOCUMENTS = 'documents';
}
