<?php

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\LayPreRegistrationDto;
use App\v2\Registration\DTO\LayPreRegistrationDtoCollection;

class LayPreRegistrationDtoCollectionAssembler
{
    public function __construct(private LayPreRegistrationDtoAssemblerInterface $layPreRegistrationDtoAssembler)
    {
    }

    public function assembleFromArray(array $data): array
    {
        $skipped = [];
        $collection = new LayPreRegistrationDtoCollection();

        foreach ($data as $line => $uploadRow) {
            try {
                $item = $this->layPreRegistrationDtoAssembler->assembleFromArray($uploadRow);
                if ($item instanceof LayPreRegistrationDto) {
                    $collection->append($item);
                }
            } catch (\InvalidArgumentException $e) {
                $skipped[] = sprintf('SKIPPED LINE %d: %s', $line + 2, $e->getMessage());
            }
        }

        return ['skipped' => $skipped, 'collection' => $collection];
    }

    public function getLayPreRegistrationDtoAssembler(): LayPreRegistrationDtoAssemblerInterface
    {
        return $this->layPreRegistrationDtoAssembler;
    }
}
