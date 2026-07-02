<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section;

enum ReportSection: string
{
    case DECISIONS = 'decisions';
    case CONTACTS = 'contacts';
    case VISITS_CARE = 'visitsCare';
    case LIFESTYLE = 'lifestyle';
    case BALANCE = 'balance'; // not a real section, but needed as a flag for the view and the validation
    case BANK_ACCOUNTS = 'bankAccounts';
    case MONEY_TRANSFERS = 'moneyTransfers';
    case MONEY_IN = 'moneyIn';
    case MONEY_OUT = 'moneyOut';
    case MONEY_IN_SHORT = 'moneyInShort';
    case MONEY_OUT_SHORT = 'moneyOutShort';
    case ASSETS = 'assets';
    case DEBTS = 'debts';
    case GIFTS = 'gifts';
    case CLIENT_BENEFITS_CHECK = 'clientBenefitsCheck';
    case ACTIONS = 'actions';
    case OTHER_INFO = 'otherInfo';
    case DEPUTY_EXPENSES = 'deputyExpenses';
    case PA_DEPUTY_EXPENSES = 'paDeputyExpenses'; // 106, AKA Fee and expenses
    case PROF_CURRENT_FEES = 'profCurrentFees';
    case PROF_DEPUTY_COSTS = 'profDeputyCosts';
    case PROF_DEPUTY_COSTS_ESTIMATE = 'profDeputyCostsEstimate';
    case DOCUMENTS = 'documents';
}
