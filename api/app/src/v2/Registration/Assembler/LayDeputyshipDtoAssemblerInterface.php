<?php

namespace OPG\Digideps\Backend\v2\Registration\Assembler;

use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;

interface LayDeputyshipDtoAssemblerInterface
{
    public function assembleFromArray(array $data): LayDeputyshipDto;
}
