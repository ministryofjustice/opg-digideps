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
        $this->assertEquals(
            ['event' => AuditEvents::CLIENT_DISCHARGED, 'trigger' => 'ADMIN_BUTTON', 'case_number' => '19348522'],
            (new AuditEvents())->clientDischarged('ADMIN_BUTTON', '19348522')
        );
    }
}
