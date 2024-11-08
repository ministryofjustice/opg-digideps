<?php

declare(strict_types=1);

namespace App\v2\Registration\SelfRegistration\Factory;

use App\v2\Registration\Assembler\LayPreRegistrationDtoAssemblerInterface;
use App\v2\Registration\Assembler\LayPreRegistrationDtoCollectionAssembler;
use App\v2\Registration\Assembler\SiriusToLayPreRegistrationDtoAssembler;

class LayPreRegistrationDtoCollectionAssemblerFactory
{
    public function create(): LayPreRegistrationDtoCollectionAssembler
    {
        $assembler = $this->buildAssemblerBySourceType();

        return new LayPreRegistrationDtoCollectionAssembler($assembler);
    }

    private function buildAssemblerBySourceType(): LayPreRegistrationDtoAssemblerInterface
    {
        return new SiriusToLayPreRegistrationDtoAssembler();
    }
}
