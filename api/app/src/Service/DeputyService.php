<?php

namespace App\Service;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Model\Hydrator;
use App\Repository\CourtOrderRepository;
use App\Repository\DeputyRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class DeputyService
{
    public function __construct(
        private readonly DeputyRepository $deputyRepository,
        private readonly CourtOrderRepository $courtOrderRepository,
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

    public function createDeputyFromPreRegistration(?PreRegistration $preReg, array $data = []): ?Deputy
    {
        if (is_null($preReg)) {
            return null;
        }

        $data = array_merge($data, [
            'firstname' => $preReg->getDeputyFirstname(),
            'lastname' => $preReg->getDeputySurname(),
            'address1' => $preReg->getDeputyAddress1(),
            'address2' => $preReg->getDeputyAddress2(),
            'address3' => $preReg->getDeputyAddress3(),
            'address4' => $preReg->getDeputyAddress4(),
            'address5' => $preReg->getDeputyAddress5(),
            'address_postcode' => $preReg->getDeputyPostcode(),
            'deputy_uid' => $preReg->getDeputyUid(),
        ]);

        return $this->populateDeputy($data);
    }

    public function findReportsInfoByUid(string $uid, bool $includeInactive = false): ?array
    {
        /** @var ?Deputy $deputy */
        $deputy = $this->deputyRepository->findOneBy(['deputyUid' => $uid]);
        if (is_null($deputy)) {
            return null;
        }

        $results = $this->courtOrderRepository->findReportsInfoByUid($deputy->getDeputyUid());
        if (is_null($results)) {
            return null;
        }

        // get the latest report for each court order, storing court order UIDs and deduplicating as we go
        $reportAggregate = [];

        foreach ($results as $courtOrderWithStatus) {
            $courtOrderUids = [];

            // whether a court order should be shown depends both on the court order status and the deputy
            // status on the order
            $show = $includeInactive || ($courtOrderWithStatus['isActive'] && $courtOrderWithStatus['status'] === 'ACTIVE');
            if (!$show) {
                continue;
            }

            if (preg_match('{,}', $courtOrderWithStatus['courtOrderUid'])) {
                $courtOrderUids = explode(', ', $courtOrderWithStatus['courtOrderUid']);
                $courtOrderLink = $courtOrderUids[0];
            } else {
                $courtOrderUids[] = $courtOrderWithStatus['courtOrderUid'];
                $courtOrderLink = $courtOrderWithStatus['courtOrderUid'];
            }

            $reportAggregate[$courtOrderWithStatus['reportId']] = [
                'client' => [
                    'firstName' => $courtOrderWithStatus['firstName'],
                    'lastName' => $courtOrderWithStatus['lastName'],
                    'caseNumber' => $courtOrderWithStatus['caseNumber'],
                ],
                'report' => [
                    'type' => $courtOrderWithStatus['type'],
                ],
                'courtOrderUids' => $courtOrderUids,
                'courtOrderLink' => $courtOrderLink,
            ];
        }

        return array_values($reportAggregate);
    }
}
