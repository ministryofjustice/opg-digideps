<?php

namespace AppBundle\v2\Assembler\CourtOrderAddress;

use AppBundle\v2\DTO\CourtOrderAddressDto;

class OrgCsvToCourtOrderAddressDtoAssembler
{
    public function assemble(array $data): CourtOrderAddressDto {
        return (new CourtOrderAddressDto())
            ->setAddressLine1($data['Dep Adrs1'])
            ->setAddressLine2($data['Dep Adrs2'])
            ->setAddressLine3($data['Dep Adrs3'])
            ->setTown($data['Dep Adrs4'])
            ->setCounty($data['Dep Adrs5'])
            ->setPostcode($data['Dep Postcode']);
    }
}
