<?php

namespace App\v2\Transformer;

use App\v2\DTO\DeputyDto;

class DeputyTransformer
{
    /**
     * @return array
     */
    public function transform(DeputyDto $dto)
    {
        $data = [
            'id' => $dto->getId(),
            'deputy_uid' => $dto->getDeputyUid(),
            'firstname' => $dto->getFirstName(),
            'lastname' => $dto->getLastName(),
            'email1' => $dto->getEmail1(),
            'email2' => $dto->getEmail2(),
            'email3' => $dto->getEmail3(),
            'phone_main' => $dto->getPhoneMain(),
            'phone_alternative' => $dto->getPhoneAlterrnative(),
            'address1' => $dto->getAddress1(),
            'address2' => $dto->getAddress2(),
            'address3' => $dto->getAddress3(),
            'address4' => $dto->getAddress4(),
            'address5' => $dto->getAddress5(),
            'address_postcode' => $dto->getAddressPostcode(),
            'address_country' => $dto->getAddressCountry(),
        ];

        return $data;
    }
}
