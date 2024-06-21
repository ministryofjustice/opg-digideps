<?php

namespace App\Factory;

use App\Entity\Deputy;

class DeputyFactory
{
    public function createFromOrgCsv(array $csvRow): Deputy
    {
        $data['deputyUid'] = $csvRow['Deputy Uid'];
        $data['deputyFirstname'] = $csvRow['Dep Forename'];
        $data['deputyLastname'] = $csvRow['Dep Surname'];
        $data['deputyEmail1'] = strtolower($csvRow['Email']);
        $data['deputyAddress1'] = $csvRow['Dep Adrs1'];
        $data['deputyAddress2'] = $csvRow['Dep Adrs2'];
        $data['deputyAddress3'] = $csvRow['Dep Adrs3'];
        $data['deputyAddress4'] = $csvRow['Dep Adrs4'];
        $data['deputyAddress5'] = $csvRow['Dep Adrs5'];
        $data['deputyAddressPostcode'] = $csvRow['Dep Postcode'];
        $data['deputyAddressCountry'] = 'GB';
        $data['deputyEmail2'] = isset($csvRow['Email2']) ? strtolower($csvRow['Email2']) : null;
        $data['deputyEmail3'] = isset($csvRow['Email3']) ? strtolower($csvRow['Email3']) : null;
        $data['deputyPhoneMain'] = $csvRow['Phone Main'] ?? null;
        $data['deputyPhoneAlternative'] = $csvRow['Phone Alternative'] ?? null;

        return $this->create($data);
    }

    private function create(array $data): Deputy
    {
        return (new Deputy())
            ->setDeputyUid($data['deputyUid'])
            ->setFirstname($data['deputyFirstname'])
            ->setLastname($data['deputyLastname'])
            ->setEmail1($data['deputyEmail1'])
            ->setEmail2($data['deputyEmail2'])
            ->setEmail3($data['deputyEmail3'])
            ->setAddress1($data['deputyAddress1'])
            ->setAddress2($data['deputyAddress2'])
            ->setAddress3($data['deputyAddress3'])
            ->setAddress4($data['deputyAddress4'])
            ->setAddress5($data['deputyAddress5'])
            ->setAddressPostcode($data['deputyAddressPostcode'])
            ->setAddressCountry($data['deputyAddressCountry'])
            ->setPhoneMain($data['deputyPhoneMain'])
            ->setPhoneAlternative($data['deputyPhoneAlternative']);
    }
}
