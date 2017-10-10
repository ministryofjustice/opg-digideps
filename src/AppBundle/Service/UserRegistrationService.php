<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Model\SelfRegisterData;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\Debug as doctrineDebug;

class UserRegistrationService
{
    /** @var EntityManager */
    private $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $casRecRepo;

    public function __construct($em)
    {
        $this->em = $em;
        $this->casRecRepo = $this->em->getRepository('AppBundle\Entity\CasRec');
    }

    /**
     * CASREC checks
     * - throw error 403 if user is a co-deputy attempting to self-register
     * - throw error 421 if user and client not found
     * - throw error 422 if user email is already found
     * - throw error 424 if user and client are found but the postcode doesn't match
     * - throw error 425 if client is already used
     * (see <root>/README.md for more info. Keep the readme file updated with this logic).
     *
     * @return User
     */
    public function selfRegisterUser(SelfRegisterData $selfRegisterData)
    {
        // Check the user doesn't already exist
        $existingUser = $this->em->getRepository('AppBundle\Entity\User')->findOneBy(['email' => $selfRegisterData->getEmail()]);
        if ($existingUser) {
            if ($existingUser->isCoDeputy()) {
                throw new \RuntimeException("Co-deputy cannot self register.", 403);
            } else {
                throw new \RuntimeException("User with email {$existingUser->getEmail()} already exists.", 422);
            }
        }

        $user = new User();
        $user->recreateRegistrationToken();
        $this->populateUser($user, $selfRegisterData);

        $client = new Client();
        $this->populateClient($client, $selfRegisterData);

        // Check the client is unique
        if (!$this->clientIsUnique($client)) {
            throw new \RuntimeException('User registration: Case number already used', 425);
        }

        // Check casRec for user
        $criteria = [ 'caseNumber'     => CasRec::normaliseCaseNumber($client->getCaseNumber())
                    , 'clientLastname' => CasRec::normaliseSurname($client->getLastname())
                    , 'deputySurname'  => CasRec::normaliseSurname($user->getLastname())
                    ];
        $casRecUserMatches = $this->getCasRecMatchesOrThrowError($criteria);

        $this->checkPostcodeExistsInCasRec($casRecUserMatches, $user->getAddressPostcode());

        // Currently unable to determine which co-deputy is matched (eg siblings at same address), based on information given
        $deputyNumbers = [];
        foreach ($casRecUserMatches as $casRecMatch) {
            $deputyNumbers[] = $casRecMatch->getDeputyNo();
        }
        $user->setDeputyNo(implode(',', $deputyNumbers));

        // For multi deputy clients
        $casRecCaseMatches = $this->getCasRecMatchesOrThrowError(['caseNumber' => CasRec::normaliseCaseNumber($client->getCaseNumber())]);
        if (count($casRecCaseMatches) > 1) {
            $user->setCoDeputyClientConfirmed(true);
        }

        $this->saveUserAndClient($user, $client);
        return $user;
    }

    /**
     * @return bool
     */
    public function validateCoDeputy(SelfRegisterData $selfRegisterData)
    {
        $user = $this->em->getRepository('AppBundle\Entity\User')->findOneBy(['email' => $selfRegisterData->getEmail()]);
        if (!($user)) {
            throw new \RuntimeException("User registration: not found", 421);
        }

        if ($user->getCoDeputyClientConfirmed()) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.", 422);
        }

        // Check casRec for user
        $criteria = [ 'caseNumber'     => CasRec::normaliseCaseNumber($selfRegisterData->getCaseNumber())
                    , 'clientLastname' => CasRec::normaliseSurname($selfRegisterData->getClientLastname())
                    , 'deputySurname'  => CasRec::normaliseSurname($selfRegisterData->getLastname())
        ];

        $casRecUserMatches = $this->getCasRecMatchesOrThrowError($criteria);
        $this->checkPostcodeExistsInCasRec($casRecUserMatches, $selfRegisterData->getPostcode());

        return true;
    }

    private function getCasRecMatchesOrThrowError($criteria)
    {
        $casRecMatches = $this->casRecRepo->findBy($criteria);
        if (count($casRecMatches) == 0) {
            throw new \RuntimeException('User registration: not found', 421);
        }
        return $casRecMatches;
    }

    /**
     * @param array $casRecUsers
     * @param string $postcode
     */
    private function checkPostcodeExistsInCasRec($casRecUsers, $postcode)
    {
        // Now that multi deputies are a thing, best we can do is ensure that the given postcode matches ONE of the postcodes
        // (Or skip this check completely it if one of the postcodes isn't set)
        $casRecPostcodes = [];
        foreach ($casRecUsers as $casRecMatch) {
            if (!empty($casRecMatch->getDeputyPostCode())) {
                $casRecPostcodes[] = $casRecMatch->getDeputyPostCode();
            }
        }
        if (count($casRecPostcodes) == count($casRecUsers)) {
            if (!in_array(CasRec::normalisePostCode($postcode), $casRecPostcodes)){
                throw new \RuntimeException('User registration: postcode mismatch', 424);
            }
        }
    }

    public function saveUserAndClient($user, $client)
    {
        $connection = $this->em->getConnection();

        $connection->beginTransaction();

        try {

            // Save the user
            $this->em->persist($user);
            $this->em->flush();

            // Add the user to the client an save it
            /* @var Client $client */
            $client->addUser($user);
            $this->em->persist($client);
            $this->em->flush();

            // Try and commit the transaction
            $connection->commit();
        } catch (\Exception $e) {
            // Rollback the failed transaction attempt
            $connection->rollback();
            throw $e;
        }
    }

    public function populateUser(User $user, SelfRegisterData $selfRegisterData)
    {
        $user->setFirstname($selfRegisterData->getFirstname());
        $user->setLastname($selfRegisterData->getLastname());
        $user->setEmail($selfRegisterData->getEmail());
        $user->setAddressPostcode($selfRegisterData->getPostcode());
        $user->setActive(false);
        $user->setRoleName(User::ROLE_LAY_DEPUTY);
        $user->setRegistrationDate(new \DateTime());
    }

    public function populateClient(Client $client, SelfRegisterData $selfRegisterData)
    {
        $client->setFirstname($selfRegisterData->getClientFirstname());
        $client->setLastname($selfRegisterData->getClientLastname());
        $client->setCaseNumber($selfRegisterData->getCaseNumber());
    }

    public function clientIsUnique(Client $client)
    {
        return !($client->getCaseNumber() && $this->em->getRepository('AppBundle\Entity\Client')->findOneBy(['caseNumber' => $client->getCaseNumber()]));
    }
}
