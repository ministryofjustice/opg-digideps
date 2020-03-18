<?php

namespace AppBundle\v2\Registration\Assembler;

use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;

class LayDeputyshipDtoCollectionAssembler
{
    /** @var LayDeputyshipDtoAssemblerInterface */
    private $layDeputyshipDtoAssembler;

    /**
     * @param LayDeputyshipDtoAssemblerInterface $layDeputyshipDtoAssembler
     */
    public function __construct(LayDeputyshipDtoAssemblerInterface $layDeputyshipDtoAssembler)
    {
        $this->layDeputyshipDtoAssembler = $layDeputyshipDtoAssembler;
    }

    /**
     * @param array $data
     * @return LayDeputyshipDtoCollection
     */
    public function assembleFromArray(array $data): LayDeputyshipDtoCollection
    {
        $collection = new LayDeputyshipDtoCollection();

        foreach ($data as $uploadRow) {
            $item = $this->layDeputyshipDtoAssembler->assembleFromArray($uploadRow);
            if ($item instanceof LayDeputyshipDto) {
                $collection->append($item);
            }
        }

        return $collection;
    }

    public function getLayDeputyshipDtoAssembler(): LayDeputyshipDtoAssemblerInterface
    {
        return $this->layDeputyshipDtoAssembler;
    }
}
