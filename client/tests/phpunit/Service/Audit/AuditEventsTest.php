<?php declare(strict_types=1);

namespace AppBundle\Service\Audit;

use PHPUnit\Framework\TestCase;

class AuditEventsTest extends TestCase
{
    /**
     * @test
     */
    public function clientDischarged(): void
    {
        $expected = [
            'event' => AuditEvents::CLIENT_DISCHARGED,
            'trigger' => 'ADMIN_BUTTON',
            'case_number' => '19348522',
            'type' => 'audit'
        ];

        $actual =  (new AuditEvents())->clientDischarged('ADMIN_BUTTON', '19348522');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function clientDischargedByUser(): void
    {
        $expected = [
            'event' => AuditEvents::CLIENT_DISCHARGED,
            'trigger' => 'ADMIN_BUTTON',
            'case_number' => '19348522',
            'discharged_by' => 'me@test.com',
            'type' => 'audit'
        ];

        $actual = (new AuditEvents())->clientDischarged('ADMIN_BUTTON', '19348522', 'me@test.com');

        $this->assertEquals($expected, $actual);
    }
}
