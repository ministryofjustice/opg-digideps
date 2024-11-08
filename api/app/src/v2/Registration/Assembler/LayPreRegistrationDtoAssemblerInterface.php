<?php

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\LayPreRegistrationDto;

interface LayPreRegistrationDtoAssemblerInterface
{
    /**
     * @return LayPreRegistrationDto
     */
    public function assembleFromArray(array $data);
}
