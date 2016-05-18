<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version049 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $idFy = function ($string) {
            return preg_replace('/(-+)/i', '-', preg_replace('/([^a-z0-9])/i', '-', strtolower($string)));
        };

        // TRANSACTIONS (sync with google doc "Deputy report card sort". Updated on 26/11/2015 12:25)
        $rows = [
            'in' => [
                'income-and-earnings' => [
                    ['account-interest'],
                    ['dividends'],
                    ['income-from-investments'],
                    ['income-from-property-rental'],
                    ['salary-or-wages'],
                ],
                'state-benefits' => [
                    ['attendance-allowance'],
                    ['disability-living-allowance'],
                    ['employment-support-allowance'],
                    ['housing-benefit'],
                    ['incapacity-benefit'],
                    ['income-support'],
                    ['pension-credit'],
                    ['personal-independence-payment'],
                    ['severe-disablement-allowance'],
                    ['universal-credit'],
                    ['winter-fuel-cold-weather-payment'],
                    ['other-benefits', true],
                ],
                'pensions' => [
                    ['personal-pension'],
                    ['state-pension'],
                ],
                'damages' => [
                    ['compensation-or-damages-award', true],
                ],
                'one-off' => [
                    ['bequest-or-inheritance'],
                    ['cash-gift-received'],
                    ['refunds'],
                    ['sale-of-asset', true],
                    ['sale-of-investment', true],
                    ['sale-of-property', true],
                ],
                'moving-money' => [
                    ['transfers-in-from-client-s-other-accounts', true],
                ],
                'moneyin-other' => [
                    ['anything-else', true],
                ],
            ],
            'out' => [
                'household-bills' => [
                    ['broadband'],
                    ['council-tax'],
                    ['electricity'],
                    ['food'],
                    ['gas'],
                    ['insurance-eg-life-home-contents'],
                    ['other-insurance'],
                    ['property-maintenance-improvement', true],
                    ['telephone'],
                    ['tv-services'],
                    ['water'],
                    ['households-bills-other', true],
                ],
                'accommodation' => [
                    ['accommodation-service-charge'],
                    ['mortgage'],
                    ['rent'],
                    ['accommodation-other', true],
                ],
                'care-and-medical' => [
                    ['care-fees'],
                    ['local-authority-charges-for-care'],
                    ['medical-expenses'],
                    ['medical-insurance'],
                ],
                'client-expenses' => [
                    ['client-transport-bus-train-taxi-fares'],
                    ['clothes'],
                    ['day-trips'],
                    ['holidays'],
                    ['personal-allowance-pocket-money'],
                    ['toiletries'],
                ],
                'fees' => [
                    ['deputy-security-bond'],
                    ['opg-fees'],
                    ['other-fees', true],
                    ['professional-fees-eg-solicitor-accountant', true],
                    ['your-deputy-expenses', true],
                ],
                'major-purchases' => [
                    ['investment-bonds-purchased', true],
                    ['investment-account-purchased', true],
                    ['purchase-over-1000', true],
                    ['stocks-and-shares-purchased', true],
                ],
                'spending-on-other-people' => [
                    ['gifts', true],
                ],
                'debt-and-charges' => [
                    ['bank-charges'],
                    ['credit-cards-charges'],
                    ['unpaid-care-fees'],
                    ['loans'],
                    ['tax-payments-to-hmrc'],
                    ['debt-and-charges-other', true],
                ],
                'moving-money' => [
                    ['cash-withdrawn', true],
                    ['transfers-out-to-other-accounts', true],
                ],
                'moneyout-other' => [
                    ['anything-else-paid-out', true],
                ],
            ],
        ];

        $displayOrder = 1;
        foreach ($rows as $type => $categories) {
            foreach ($categories as $category => $transactions) { //in / out
                foreach ($transactions as $transaction) {
                    if (!is_array($transaction)) {
                        throw new \Exception('format error for transaction');
                    }
                    ++$displayOrder;
                    $id = $transaction[0];
                    $hasMoreDetailsBool = empty($transaction[1]) ? 'false' : 'true';
                    $sql = "INSERT INTO transaction_type (display_order, type, id, has_more_details, category)
                      VALUES('$displayOrder', '$type', '$id', $hasMoreDetailsBool, '$category');";
                    $this->addSql($sql);
                }
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('TRUNCATE TABLE transaction_type, transaction;');
    }
}
