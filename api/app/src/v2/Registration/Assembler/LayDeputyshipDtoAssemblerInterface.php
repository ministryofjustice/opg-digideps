<?php

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\LayDeputyshipDto;

interface LayDeputyshipDtoAssemblerInterface
{
    /**
     * @return LayDeputyshipDto
     */
    public function assembleFromArray(array $data);
}
