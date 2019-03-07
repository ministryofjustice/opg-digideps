<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\ClientDto;

class ClientAssembler
{
    /**
     * @param array $data
     * @return ClientDto
     */
    public function assembleFromArray(array $data)
    {
        $this->throwExceptionIfMissingRequiredData($data);

        return new ClientDto(
            $data['id'],
            $data['case_number'],
            $data['firstname'],
            $data['lastname'],
            $data['email'],
            $data['report_count'],
            $data['ndr_id']
        );
    }

    /**
     * @param array $data
     */
    private function throwExceptionIfMissingRequiredData(array $data)
    {
        if (!$this->dataIsValid($data)) {
            throw new \InvalidArgumentException(__CLASS__ . ': Missing all data required to build DTO');
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    private function dataIsValid(array $data)
    {
        return
            array_key_exists('id', $data) &&
            array_key_exists('case_number', $data) &&
            array_key_exists('firstname', $data) &&
            array_key_exists('lastname', $data) &&
            array_key_exists('email', $data) &&
            array_key_exists('report_count', $data) &&
            array_key_exists('ndr_id', $data);
    }
}
