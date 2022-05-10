<?php

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\DTO\LayDeputyshipDtoCollection;

class LayDeputyshipDtoCollectionAssembler
{
    /** @var LayDeputyshipDtoAssemblerInterface */
    private $layDeputyshipDtoAssembler;

    public function __construct(LayDeputyshipDtoAssemblerInterface $layDeputyshipDtoAssembler)
    {
        $this->layDeputyshipDtoAssembler = $layDeputyshipDtoAssembler;
    }

    public function assembleFromArray(array $data): array
    {
        $skipped = [];
        $collection = new LayDeputyshipDtoCollection();

        foreach ($data as $line => $uploadRow) {
            $item = $this->layDeputyshipDtoAssembler->assembleFromArray($uploadRow);
            if ($item instanceof LayDeputyshipDto) {
                $collection->append($item);
            } else {
                $skipped[] = sprintf('SKIPPED LINE %d:', $line + 2);
            }
        }

        return ['skipped' => $skipped, 'collection' => $collection];
    }

    public function getLayDeputyshipDtoAssembler(): LayDeputyshipDtoAssemblerInterface
    {
        return $this->layDeputyshipDtoAssembler;
    }
}
