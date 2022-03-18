<?php

namespace App\v2\Registration\SelfRegistration\Factory;

use App\Entity\CasRec;
use App\Service\DataNormaliser;
use App\v2\Registration\Assembler\CasRecToLayDeputyshipDtoAssembler;
use App\v2\Registration\Assembler\LayDeputyshipDtoAssemblerInterface;
use App\v2\Registration\Assembler\LayDeputyshipDtoCollectionAssembler;
use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use InvalidArgumentException;

class LayDeputyshipDtoCollectionAssemblerFactory
{
    public function create(array $uploadedData): LayDeputyshipDtoCollectionAssembler
    {
        $source = 'sirius';
        $assembler = $this->buildAssemblerBySourceType($source);

        return new LayDeputyshipDtoCollectionAssembler($assembler);
    }

    private function determineSource(array $postedData)
    {
        // Absence of a source implies it is from CasRec.
        if (!isset($postedData[0]['Source'])) {
            return CasRec::CASREC_SOURCE;
        }

        // Not expected, but if invalid source, fallback to casrec, for now at least.
        if (!in_array($postedData[0]['Source'], CasRec::validSources())) {
            return CasRec::CASREC_SOURCE;
        }

        return $postedData[0]['Source'];
    }

    private function buildAssemblerBySourceType(string $source): LayDeputyshipDtoAssemblerInterface
    {
        switch ($source) {
            case CasRec::CASREC_SOURCE:
                return new CasRecToLayDeputyshipDtoAssembler(new DataNormaliser());
            case CasRec::SIRIUS_SOURCE:
                return new SiriusToLayDeputyshipDtoAssembler(new DataNormaliser());
            default:
                throw new InvalidArgumentException(sprintf('Unable to build assembler from unknown source: %s', $source));
        }
    }
}
