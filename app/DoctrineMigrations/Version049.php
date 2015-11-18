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

        // TRANSACTIONS
        // income_earnings, state_benefits, pensions, one_off_debits, damages_and_awards, other
        $rows = [
            'in' => [
                'income/earnings' => [
                    ['dividends', false],
                    ['income-from-investments', false],
                    ['account-interest', false],
                    ['salary-or-wages', false],
                    ['income-from-property-rental', false],
                ],
                'state-benefits' => [
                    ['attendance-allowance', false],
                    ['disability-living-allowance', false],
                    ['personal-independence-payment', false],
                    ['severe-disablement-allowance', false],
                    ['winter-fuel/cold-weather-payment', false],
                    ['housing-benefit', false],
                    ['employment-support-allowance', false],
                    ['incapacity-benefit', false],
                    ['income-support', false],
                    ['pension-credit', false],
                    ['universal-credit', false],
                ],
                'pensions' => [
                    ['personal-pension', false],
                    ['state-pension', false],
                ],
                'damages' => [
                    ['compensation-or-damages-award', true],
                ],
                'one-off' => [
                    ['bequest-received', false],
                    ['gift-received', false],
                    ['sale-of-property', true],
                    ['sale-of-asset', true],
                ],
                'moving-money' => [
                    ['transfers-in-from-client-s-other-accounts', true],
                ],
                'other' => [
                    ['refunds', false],
                    ['anything-else', false],
                ],
            ],
            'out' => [
                'household-bills' => [
                    ['water', false],
                    ['gas', false],
                    ['electricity', false],
                    ['telephone', false],
                    ['council-tax', false],
                    ['property-maintenance/improvement', false],
                    ['rent', false],
                    ['mortgage', false],
                    ['accommodation-service-charge', false],
                    ['buildings-or-contents-insurance', false],
                ],
                'care' => [
                    ['care-fees', false],
                    ['medical-expenses', false],
                    ['medical-insurance', false],
                ],
                'client-spending-money' => [
                    ['food', false],
                    ['toiletries', false],
                    ['clothes', false],
                    ['personal-allowance/pocket-money', false],
                    ['holidays', false],
                    ['day-trips', false],
                    ['transport-bus,-train,-taxi-fares', false],
                ],
                'fees' => [
                    ['tax-payments-to-hmrc', false],
                    ['security-bond', false],
                    ['solicitor-fees', true],
                    ['accountant-fees', true],
                    ['opg-fees', false],
                ],
                'major-purchases' => [
                    ['stocks-or-shares', false],
                    ['investment-bonds', false],
                    ['investment-account', true],
                    ['purchase-over-1000', true],
                ],
                'spending-on-other-people' => [
                    ['deputy-expenses', true],
                    ['gifts', true],
                ],
                'debt-payments' => [
                    ['loans', false],
                    ['credit-cards', false],
                    ['arrears', false],
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
                    list($id, $hasMoreDetails) = $transaction;
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
