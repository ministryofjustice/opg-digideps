<?php

namespace AppBundle\Factory;

use AppBundle\Entity\NamedDeputy;

class NamedDeputyFactory
{

    /**
     * @param array $csvRow
     * @return NamedDeputy
     */
    public function createFromOrgCsv(array $csvRow): NamedDeputy
    {
        $data['deputyNo'] = $csvRow['Deputy No'];
        $data['deputyType'] = $csvRow['Dep Type'];
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
        $data['deputyAddressNo'] = isset($csvRow['DepAddr No']) ? $csvRow['DepAddr No'] : null;
        $data['deputyPhoneMain'] = isset($csvRow['Phone Main']) ? $csvRow['Phone Main'] : null;
        $data['deputyPhoneAlternative'] = isset($csvRow['Phone Alternative']) ? $csvRow['Phone Alternative'] : null;
        $data['feePayer'] = isset($csvRow['Fee Payer']) && $csvRow['Fee Payer'] == 'Y' ? true : null;
        $data['corres'] = isset($csvRow['Corres']) && $csvRow['Corres'] == 'Y' ? true : null;

        return $this->create($data);
    }

    /**
     * @param array $data
     * @return NamedDeputy
     */
    private function create(array $data): NamedDeputy
    {
        return (new NamedDeputy())
            ->setDeputyNo($data['deputyNo'])
            ->setFirstname($data['deputyFirstname'])
            ->setLastname($data['deputyLastname'])
            ->setEmail1($data['deputyEmail1'])
            ->setEmail2($data['deputyEmail2'])
            ->setEmail3($data['deputyEmail3'])
            ->setDeputyType($data['deputyType'])
            ->setFeePayer($data['feePayer'])
            ->setCorres($data['corres'])
            ->setDepAddrNo($data['deputyAddressNo'])
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
