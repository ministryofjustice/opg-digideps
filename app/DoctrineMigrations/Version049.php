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
                'income-earnings' => [
                    ['dividends'],
                    ['income-from-investments'],
                    ['account-interest'],
                    ['salary-or-wages'],
                    ['income-from-property-rental'],
                ],
                'state-benefits' => [
                    ['attendance-allowance'],
                    ['disability-living-allowance'],
                    ['personal-independence-payment'],
                    ['severe-disablement-allowance'],
                    ['winter-fuel-cold-weather-payment'],
                    ['housing-benefit'],
                    ['employment-support-allowance'],
                    ['incapacity-benefit'],
                    ['income-support'],
                    ['pension-credit'],
                    ['universal-credit'],
                ],
                'pensions' => [
                    ['personal-pension'],
                    ['state-pension'],
                ],
                'damages' => [
                    ['compensation-or-damages-award', true],
                ],
                'one-off' => [
                    ['bequest-received'],
                    ['gift-received'],
                    ['sale-of-property', true],
                    ['sale-of-asset', true],
                ],
                'moving-money' => [
                    ['transfers-in-from-client-s-other-accounts', true],
                ],
                'other' => [
                    ['refunds'],
                    ['anything-else'],
                ],
            ],
            'out' => [
                'household-bills' => [
                    ['water'],
                    ['gas'],
                    ['electricity'],
                    ['telephone'],
                    ['council-tax'],
                    ['property-maintenance-improvement', true],
                    ['rent'],
                    ['mortgage'],
                    ['accommodation-service-charge'],
                    ['buildings-or-contents-insurance'],
                ],
                'care' => [
                    ['care-fees'],
                    ['medical-expenses'],
                    ['medical-insurance'],
                ],
                'client-spending-money' => [
                    ['food'],
                    ['toiletries'],
                    ['clothes'],
                    ['personal-allowance-pocket-money'],
                    ['holidays'],
                    ['day-trips'],
                    ['transport-bus-train-taxi-fares'],
                ],
                'fees' => [
                    ['tax-payments-to-hmrc'],
                    ['security-bond'],
                    ['solicitor-fees', true],
                    ['accountant-fees', true],
                    ['opg-fees'],
                ],
                'major-purchases' => [
                    ['stocks-or-shares'],
                    ['investment-bonds'],
                    ['investment-account', true],
                    ['purchase-over-1000', true],
                ],
                'spending-on-other-people' => [
                    ['deputy-expenses', true],
                    ['gifts', true],
                ],
                'debt-payments' => [
                    ['loans'],
                    ['credit-cards'],
                    ['arrears'],
                ],
                'moving-money' => [
                    ['transfers-out-to-other-client-accounts', true],
                    ['cash-withdrawn', true],
                ],
            ],
        ];


        $displayOrder = 1;
        foreach ($rows as $type => $categories) {
            foreach ($categories as $category => $transactions) { //in / out
                foreach ($transactions as $transaction) {
                    $displayOrder++;
                    $id = $transaction[0];
                    $hasMoreDetails = !empty($transaction[1]);
                    $hasMoreDetailsBool = $hasMoreDetails ? 'true' : 'false';
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

        $this->addSql('TRUNCATE TABLE transaction_type');
        $this->addSql('TRUNCATE TABLE transaction_type_category');
    }
}
