<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\SelfRegistration\Factory;

use PHPUnit\Framework\Attributes\Test;
use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use App\v2\Registration\SelfRegistration\Factory\LayDeputyshipDtoCollectionAssemblerFactory;
use PHPUnit\Framework\TestCase;

final class LayDeputyshipDtoCollectionAssemblerFactoryTest extends TestCase
{
    #[Test]
    public function createsSiriusAssemblerWhenSourceIsSirius(): void
    {
        $assembler = (new LayDeputyshipDtoCollectionAssemblerFactory())->create([['Source' => 'sirius']]);
        $this->assertInstanceOf(SiriusToLayDeputyshipDtoAssembler::class, $assembler->getLayDeputyshipDtoAssembler());
    }
}
