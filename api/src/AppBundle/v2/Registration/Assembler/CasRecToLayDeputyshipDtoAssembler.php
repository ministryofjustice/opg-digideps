<?php

namespace AppBundle\v2\Registration\Assembler;

use AppBundle\Service\DataNormaliser;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;

class CasRecToLayDeputyshipDtoAssembler implements LayDeputyshipDtoAssemblerInterface
{
    /** @var DataNormaliser */
    private $normaliser;

    /**
     * @param DataNormaliser $normaliser
     */
    public function __construct(DataNormaliser $normaliser)
    {
        $this->normaliser = $normaliser;
    }

    /**
     * @param array $data
     * @return LayDeputyshipDto
     */
    public function assembleFromArray(array $data)
    {
        if (!$this->canAssemble($data)) {
            throw new \InvalidArgumentException('Cannot assemble LayDeputyshipDto: Missing expected data');
        }

        return
            (new LayDeputyshipDto())
            ->setCaseNumber($this->normaliser->normaliseCaseNumber($data['Case']))
            ->setClientSurname($this->normaliser->normaliseSurname($data['Surname']))
            ->setDeputyNumber($this->normaliser->normaliseDeputyNo($data['Deputy No']))
            ->setDeputySurname($this->normaliser->normaliseSurname($data['Dep Surname']))
            ->setDeputyPostcode($this->normaliser->normalisePostCode($data['Dep Postcode']))
            ->setTypeOfReport($data['Typeofrep'])
            ->setCorref($data['Corref'])
            ->setIsNdrEnabled($this->determineNdrStatus($data['NDR']));
    }

    /**
     * @param array $data
     * @return bool
     */
    private function canAssemble(array $data)
    {
        return
            array_key_exists('Case', $data) &&
            array_key_exists('Surname', $data) &&
            array_key_exists('Deputy No', $data) &&
            array_key_exists('Dep Surname', $data) &&
            array_key_exists('Dep Postcode', $data) &&
            array_key_exists('Typeofrep', $data) &&
            array_key_exists('Corref', $data) &&
            array_key_exists('NDR', $data);
    }

    /**
     * @param $value
     * @return bool
     */
    private function determineNdrStatus($value): bool
    {
        return ($value === 1 || $value === 'Y') ? true : false;
    }
}
