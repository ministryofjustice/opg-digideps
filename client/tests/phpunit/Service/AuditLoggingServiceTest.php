<?php declare(strict_types=1);

namespace AppBundle\Service;


class AuditLoggingServiceTest
{
    /** @test */
    public function logShouldBeFired()
    {
        $sut = new AuditLoggingService();

        $sut->logShouldBeFired();
    }
}
