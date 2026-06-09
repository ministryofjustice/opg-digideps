<?php

namespace OPG\Digideps\Backend\v2\Registration\Assembler;

use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDtoCollection;

class LayDeputyshipDtoCollectionAssembler
{
    public function __construct(private readonly LayDeputyshipDtoAssemblerInterface $layDeputyshipDtoAssembler)
    {
    }

    public function assembleFromArray(array $data): array
    {
        $skipped = [];
        $collection = new LayDeputyshipDtoCollection();

        foreach ($data as $line => $uploadRow) {
            try {
                $collection->append($this->layDeputyshipDtoAssembler->assembleFromArray($uploadRow));
            } catch (\InvalidArgumentException $e) {
                $skipped[] = sprintf('SKIPPED LINE %d: %s', $line + 2, $e->getMessage());
            }
        }

        return ['skipped' => $skipped, 'collection' => $collection];
    }

    public function getLayDeputyshipDtoAssembler(): LayDeputyshipDtoAssemblerInterface
    {
        return $this->layDeputyshipDtoAssembler;
    }
}
