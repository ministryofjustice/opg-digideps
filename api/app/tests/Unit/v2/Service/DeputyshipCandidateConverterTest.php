<?php

declare(strict_types=1);

namespace App\v2\Service;

use PHPUnit\Framework\TestCase;

class DeputyshipCandidateConverterTest extends TestCase
{
    private DeputyshipCandidateConverter $sut;

    public function setUp(): void
    {
        $this->sut = new DeputyshipCandidateConverter();
    }
}
