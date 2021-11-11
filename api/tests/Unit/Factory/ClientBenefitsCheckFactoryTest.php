<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;

class ClientBenefitsCheckFactoryTest extends TestCase
{
    public function createFromFormData()
    {
        $validData = [
            'report_id' => 1436,
            'id' => "8e3aaf2c-3145-4e07-b64b-37702323c6f9",
            'created' => "2021-11-11",
            'when_last_checked_entitlement' => "haveChecked",
            'date_last_checked_entitlement' => "2021-11-11",
            'never_checked_explanation' => null,
            'do_others_receive_income_on_clients_behalf' => "yes",
            'dont_know_income_explanation' => null,
            'types_of_income_received_on_clients_behalf' => [
                0 => [
                    'id' => "5d80a2f3-4f2c-4e0f-9709-2d201102cb13",
                    'created' => "2021-11-11",
                    'client_benefits_check' => null,
                    'income_type' => "Universal Credit",
                    'amount' => 100.5,
                    'amount_dont_know' => null,
                ],
                1 => [
                    'id' => "5d80a2f3-4f2c-4e0f-9709-2d201102cb13",
                    'created' => "2021-11-11",
                    'client_benefits_check' => null,
                    'income_type' => "Universal Credit",
                    'amount' => 100.5,
                    'amount_dont_know' => null,
                ],
            ],
            'report' => [],
        ];
}
