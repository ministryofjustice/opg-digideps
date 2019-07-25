<?php

namespace AppBundle\v2\Registration\Assembler;

use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;

class LayDeputyshipDtoCollectionAssembler
{
    /** @var CasRecToLayDeputyshipDtoAssembler */
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
    public function assembleFromArray(array $data)
    {
        $collection = new LayDeputyshipDtoCollection();

        foreach ($data as $uploadRow) {
            $item = $this->layDeputyshipDtoAssembler->assembleFromArray($uploadRow);
            $collection->append($item);
        }

        return $collection;
    }
}
