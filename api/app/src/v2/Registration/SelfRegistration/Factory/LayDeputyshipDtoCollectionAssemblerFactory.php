<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\SelfRegistration\Factory;

use OPG\Digideps\Backend\v2\Registration\Assembler\LayDeputyshipDtoAssemblerInterface;
use OPG\Digideps\Backend\v2\Registration\Assembler\LayDeputyshipDtoCollectionAssembler;
use OPG\Digideps\Backend\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;

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
