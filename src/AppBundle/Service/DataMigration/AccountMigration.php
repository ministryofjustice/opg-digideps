<?php

namespace AppBundle\Service\DataMigration;

use Doctrine\DBAL\Connection;
use PDO;

class AccountMigration
{
    /**
     * @var Connection
     */
    private $pdo;

    private static $transactionMap = [
        'disability_living_allowance_or_personal_independence_payment' => 'disability-living-allowance',
        'attendance_allowance' => 'attendance-allowance',
        'employment_support_allowance_or_incapacity_benefit' => 'employment-support-allowance',
        'severe_disablement_allowance' => 'severe-disablement-allowance',
        'income_support_or_pension_credit' => 'income-support',
        'housing_benefit' => 'housing-benefit',
        'state_pension' => 'state-pension',
        'universal_credit' => 'universal-credit',
        'other_benefits_eg_winter_fuel_or_cold_weather_payments' => 'other-benefits',
        'occupational_pensions' => 'personal-pension',
        'account_interest' => 'account-interest',
        'income_from_investments_property_or_dividends' => 'income-from-investments', // / dividends / income-from-property-rental
        'salary_or_wages' => 'salary-or-wages',
        'refunds' => 'refunds',
        'bequests_eg_inheritance_gifts_received' => 'bequest-or-inheritance',
        'sale_of_investments_property_or_assets' => 'sale-of-investment', // / sale-of-property  / sale-of-asset
        'compensation_or_damages_awards' => 'compensation-or-damages-award',
        'transfers_in_from_client_s_other_accounts' => 'transfers-in-from-client-s-other-accounts',
        'any_other_money_paid_in_and_not_listed_above' => 'anything-else',
        'care_fees_or_local_authority_charges_for_care' => 'care-fees', // / local-authority-charges-for-care
        'accommodation_costs_eg_rent_mortgage_service_charges' => 'accommodation-other', // / mortgage / rent
        'household_bills_eg_water_gas_electricity_phone_council_tax' => 'households-bills-other',
        'day_to_day_living_costs_eg_food_toiletries_clothing_sundries' => 'toiletries',
        'debt_payments_eg_loans_cards_care_fee_arrears' => 'debt-and-charges-other', // / loans
        'travel_costs_for_client_eg_bus_train_taxi_fares' => 'client-transport-bus-train-taxi-fares',
        'holidays_or_day_trips' => 'holidays', // / holidays
        'tax_payable_to_hmrc' => 'tax-payments-to-hmrc',
        'insurance_eg_life_home_and_contents' => 'insurance-eg-life-home-contents',
        'office_of_the_public_guardian_fees' => 'opg-fees',
        'deputy_s_security_bond' => 'deputy-security-bond',
        'client_s_personal_allowance_eg_spending_money' => 'personal-allowance-pocket-money',
        'cash_withdrawals' => 'cash-withdrawn',
        'professional_fees_eg_solicitor_or_accountant_fees' => 'professional-fees-eg-solicitor-accountant',
        'deputy_s_expenses' => 'your-deputy-expenses',
        'gifts' => 'gifts',
        'major_purchases_eg_property_vehicles' => 'purchase-over-1000',
        'property_maintenance_or_improvement' => 'property-maintenance-improvement',
        'investments_eg_shares_bonds_savings' => 'investment-bonds-purchased',
        'transfers_out_to_other_client_s_accounts' => 'transfers-out-to-other-accounts',
        'any_other_money_paid_out_and_not_listed_above' => 'anything-else-paid-out',
    ];

    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function migrateAll()
    {
        $reports = $this->getReports();

//        $oldTypes = $this->fetchAll("SELECT * from account_transaction_type");
//        $newTypes = $this->fetchAll("SELECT * from transaction_type");

        // DEBUG Data
//        $toCsv = function($rows) {
//            $ret = '';
//            foreach($rows as $row) {
//                $ret .= implode(', ', $row) . "\n";
//            }
//            return $ret;
//        };
//        file_put_contents(__DIR__ . '/types.old.csv', $toCsv($oldTypes, true));
//        file_put_contents(__DIR__ . '/types.new.csv', $toCsv($newTypes, true));

        // merge transaction into array
        // sum amounts and string-contact more_details stuff
        $transactionsToInsert = [];
        $explanationsToAdd = [];
        foreach ($reports as $reportId => $reportData) {
            foreach ($reportData['accounts'] as $account) {
                // merge transactions
                foreach ($account['transactions_old'] as $typeId => $tRow) {
                    if (!isset(self::$transactionMap[$typeId])) {
                        throw new \RuntimeException("cannot map old transaction type [$typeId]");
                    }
                    $newTypeId = self::$transactionMap[$typeId];
                    if (!isset($transactionsToInsert[$reportId][$newTypeId])) {
                        $transactionsToInsert[$reportId][$newTypeId] = ['amount' => 0.0, 'more_details' => null];
                    }
                    $transactionsToInsert[$reportId][$newTypeId]['amount'] += $tRow['amount'];
                    $transactionsToInsert[$reportId][$newTypeId]['more_details'][] = $tRow['more_details'];
                }
                // closing balance explanations
                if (!empty($account['closing_balance_explanation'])) {
                    $explanationsToAdd[$reportId][] = [$account['bank_name'], $account['closing_balance_explanation']];
                }
            }
        }

        // add transactions
        $stmt = $this->pdo->prepare(
            'INSERT INTO transaction(report_id, transaction_type_id, amount, more_details)'
            .' VALUES(:id, :transaction_type_id, :amount, :md)');

        $added = 0;
        foreach ($transactionsToInsert as $reportId => $row) {
            foreach ($row as $newTypeId => $t) {
                $params = [
                    ':id' => $reportId,
                    ':transaction_type_id' => $newTypeId,
                    ':amount' => $t['amount'],
                    ':md' => implode("\n", array_filter($t['more_details'])),
                ];
                $stmt->execute($params);
                ++$added;
            }
        }

        // update explanations
        $stmt = $this->pdo->prepare(
            'UPDATE report SET balance_mismatch_explanation = :balance_mismatch_explanation WHERE id=:report_id');
        foreach ($explanationsToAdd as $reportId => $explanations) {
            $explanationStrings = [];
            foreach ($explanations as $bankAndExplanation) {
                $explanationStrings[] = $bankAndExplanation[0].': '.$bankAndExplanation[1];
            }
            $params = [
                ':report_id' => $reportId,
                ':balance_mismatch_explanation' => implode("\n", $explanationStrings),
            ];
            $stmt->execute($params);
        }
    }

    public function addMissingTransactions()
    {
        $transactionTypes = $this->fetchAll('SELECT * from transaction_type');

        // add transactions
        $stmt = $this->pdo->prepare(
            'INSERT INTO transaction(report_id, transaction_type_id, amount, more_details)'
            .' VALUES(:id, :transaction_type_id, :amount, :md)');

        $ret = [];
        $reports = $this->getReports();
        foreach ($reports as $report) {
            $reportId = $report['id'];
            $ret[$reportId]['added'] = 0;
            $ret[$reportId]['before'] = $this->getTransactionNumber($reportId);
            foreach ($transactionTypes as $transactionTypeId => $row) {
                $containsTransaction = isset($report['transactions_new'][$transactionTypeId]);
                if (!$containsTransaction) {
                    $params = [
                        ':id' => $report['id'],
                        ':transaction_type_id' => $transactionTypeId,
                        ':amount' => null,
                        ':md' => null,
                    ];
                    $stmt->execute($params);
                    ++$ret[$reportId]['added'];
                }
            }
            $ret[$reportId]['after'] = $this->getTransactionNumber($reportId);
        }

        return $ret;
    }

    private function getTransactionNumber($reportId)
    {
        return $this->pdo->query('SELECT COUNT(*) FROM transaction WHERE report_id = '.$reportId)->fetch(\PDO::FETCH_COLUMN);
    }

    public function getReports()
    {
        $reports = $this->fetchAll('SELECT * from report');

        foreach ($reports as $k => $report) {
            // add new transactions
            $reports[$k]['transactions_new'] =
                $this->fetchAll('SELECT * from transaction  t
                        LEFT JOIN transaction_type at
                        ON t.transaction_type_id = at.id
                        WHERE report_id = '.$report['id']);
            $reports[$k]['transactions_new_sum'] =
                $this->calculateAmountsTotal($reports[$k]['transactions_new']);

            // add accounts
            $reports[$k]['accounts'] = $this->fetchAll('SELECT * from account WHERE report_id='.$report['id']);

            // add old transaction to account
            foreach ($reports[$k]['accounts'] as $ka => $account) {
                $reports[$k]['accounts'][$ka]['transactions_old'] =
                    $this->fetchAll('SELECT * from account_transaction at
                        LEFT JOIN account_transaction_type att
                        ON at.account_transaction_type_id = att.id

                        WHERE account_id = '.$account['id'], 'account_transaction_type_id');
                $reports[$k]['accounts'][$ka]['transactions_old_sum'] =
                    $this->calculateAmountsTotal($reports[$k]['accounts'][$ka]['transactions_old']);
            }
        }

        return $reports;
    }

    /**
     * @param array  $transactions array of [type=>in/out, amount=>integer]
     * @param string $key
     *
     * @return array of [in=>integer sum of the amount with type=in, out=>integer sum of the amount with type=out]
     */
    private function calculateAmountsTotal(array $transactions)
    {
        $ret = ['in' => 0.0, 'out' => 0.0];

        foreach ($transactions as $t) {
            $ret[$t['type']] += $t['amount'];
        }

        return $ret;
    }

    /**
     * Return query ASSOC results, using $key as ID.
     *
     * @param string $query
     * @param string $key
     *
     * @return array
     */
    private function fetchAll($query, $key = 'id')
    {
        $results = $this->pdo->query($query)->fetchAll();

        $ret = [];
        foreach ($results as $result) {
            $keyValue = $result[$key];
            $ret[$keyValue] = $result;
        }

        return $ret;
    }
}
