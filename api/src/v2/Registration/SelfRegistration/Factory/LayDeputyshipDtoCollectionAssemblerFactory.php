<?php

declare(strict_types=1);

namespace App\v2\Registration\SelfRegistration\Factory;

use App\v2\Registration\Assembler\LayDeputyshipDtoAssemblerInterface;
use App\v2\Registration\Assembler\LayDeputyshipDtoCollectionAssembler;
use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;

class LayDeputyshipDtoCollectionAssemblerFactory
{
    public function create(): LayDeputyshipDtoCollectionAssembler
    {
        $assembler = $this->buildAssemblerBySourceType();

        return new LayDeputyshipDtoCollectionAssembler($assembler);
    }

    private function buildAssemblerBySourceType(): LayDeputyshipDtoAssemblerInterface
    {
        return new SiriusToLayDeputyshipDtoAssembler();
    }
}
