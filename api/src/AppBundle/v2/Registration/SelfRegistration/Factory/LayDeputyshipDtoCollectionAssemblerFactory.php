<?php

namespace AppBundle\v2\Registration\SelfRegistration\Factory;

use AppBundle\Entity\CasRec;
use AppBundle\Service\DataNormaliser;
use AppBundle\v2\Registration\Assembler\CasRecToLayDeputyshipDtoAssembler;
use AppBundle\v2\Registration\Assembler\LayDeputyshipDtoAssemblerInterface;
use AppBundle\v2\Registration\Assembler\LayDeputyshipDtoCollectionAssembler;
use AppBundle\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;

class LayDeputyshipDtoCollectionAssemblerFactory
{
    /**
     * @param array $uploadedData
     * @return LayDeputyshipDtoCollectionAssembler
     */
    public function create(array $uploadedData): LayDeputyshipDtoCollectionAssembler
    {
        $source = $this->determineSource($uploadedData);
        $assembler = $this->buildAssemblerBySourceType($source);

        return new LayDeputyshipDtoCollectionAssembler($assembler);
    }

    private function determineSource(array $postedData)
    {
        // Absence of a source implies it is from CasRec.
        if (!isset($postedData[0]['source'])) {
            return CasRec::CASREC_SOURCE;
        }

        // Not expected, but if invalid source, fallback to casrec, for now at least.
        if (!in_array($postedData[0]['source'], CasRec::validSources())) {
            return CasRec::CASREC_SOURCE;
        }

        return $postedData[0]['source'];
    }

    private function buildAssemblerBySourceType(string $source): LayDeputyshipDtoAssemblerInterface
    {
        switch ($source) {
            case CasRec::CASREC_SOURCE:
                return new CasRecToLayDeputyshipDtoAssembler(new DataNormaliser());
            case CasRec::SIRIUS_SOURCE:
                return new SiriusToLayDeputyshipDtoAssembler(new DataNormaliser());
            default:
                throw new \InvalidArgumentException(sprintf('Unable to build assembler from unknown source: %s', $source));
        }
    }
}
