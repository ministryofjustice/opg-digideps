<?php

namespace App\Service;

use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Model\Hydrator;
use App\Repository\DeputyRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeputyService
{
    public function __construct(
        private readonly DeputyRepository $deputyRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Adds a new deputy to the database if it doesn't already exist, or retrieve any existing one.
     * $userForDeputy becomes the user associated with the deputy if there is no existing deputy.
     */
    public function getOrAddDeputy(Deputy $deputyToAdd, User $userForDeputy): Deputy
    {
        $existingDeputy = $this->deputyRepository->findOneBy(['deputyUid' => $deputyToAdd->getDeputyUid()]);
        if ($existingDeputy) {
            return $existingDeputy;
        }

        $deputyToAdd->setUser($userForDeputy);
        $this->em->persist($deputyToAdd);
        $this->em->flush();

        return $deputyToAdd;
    }

    public function populateDeputy(array $data, ?Deputy $deputy = null): Deputy
    {
        if (is_null($deputy)) {
            $deputy = new Deputy();
        }

        Hydrator::hydrateEntityWithArrayData($deputy, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'address1' => 'setAddress1',
            'address2' => 'setAddress2',
            'address3' => 'setAddress3',
            'address4' => 'setAddress4',
            'address5' => 'setAddress5',
            'address_postcode' => 'setAddressPostcode',
            'address_country' => 'setAddressCountry',
            'phone_alternative' => 'setPhoneAlternative',
            'phone_main' => 'setPhoneMain',
        ]);

        if (array_key_exists('email', $data) && !empty($data['email'])) {
            $deputy->setEmail1($data['email']);
        }

        if (array_key_exists('deputy_uid', $data) && !empty($data['deputy_uid'])) {
            $deputy->setDeputyUid($data['deputy_uid']);
        }

        return $deputy;
    }

    public function createDeputyFromPreRegistration(PreRegistration $preReg): Deputy
    {
        $data = [
            'firstname' => $preReg->getDeputyFirstname(),
            'lastname' => $preReg->getDeputySurname(),
            'address1' => $preReg->getDeputyAddress1(),
            'address2' => $preReg->getDeputyAddress2(),
            'address3' => $preReg->getDeputyAddress3(),
            'address4' => $preReg->getDeputyAddress4(),
            'address5' => $preReg->getDeputyAddress5(),
            'address_postcode' => $preReg->getDeputyPostcode(),
            'deputy_uid' => $preReg->getDeputyUid(),
        ];

        $deputy = $this->populateDeputy($data);

        $this->em->persist($deputy);
        $this->em->flush();

        return $deputy;
    }
}
