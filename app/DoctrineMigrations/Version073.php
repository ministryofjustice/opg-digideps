<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version073 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        foreach([
            'ISAs' => 'isa', //(239)
            'Court Funds Office accounts' => 'cfo', // (21)
            'Savings accounts' => 'savings' //(233)
        ] as $title => $accountType) {
            $assets = $this->connection->query("
            select * from asset 
            WHERE title ='{$title}'")->fetchAll();
            foreach ($assets as $asset) {
                $account = [
                    'report_id' => $asset['report_id'],
                    'bank_name' => $asset['description'].' (created from existing asset)',
                    'account_number' => null,
                    'opening_balance' =>  $asset['asset_value'],
                    'closing_balance' => $asset['asset_value'],
                    'opening_date' => null,
                    'closing_date' => null,
                    'created_at' => date('Y-m-d'),
                    'account_type' => $accountType,
                    'is_closed' => 'false',
                    'is_joint_account' => null
                ];
                //echo str_replace(["\n", "\r", "\r\n"], " ", implode(';', $asset). ';' . implode(';', $account)) . "\n";
                $this->connection->insert('account', $account);
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
    }

}
