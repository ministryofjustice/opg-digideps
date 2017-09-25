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
    private $userRepository;

    /** @var \Doctrine\ORM\EntityRepository */
    private $casRecRepo;

    public function __construct($em)
    {
        $this->em = $em;
        $this->casRecRepo = $this->em->getRepository('AppBundle\Entity\CasRec');
    }

    /**
     * CASREC checks
     * - throw error 421 if user and client not found
     * - throw error 424 if user and client are found but the postcode doesn't match
     * (see <root>/README.md for more info. Keep the readme file updated with this logic).
     *
     * @return User
     */
    public function selfRegisterUser(SelfRegisterData $selfRegisterData)
    {
        $user = new User();
        $client = new Client();

        $user->recreateRegistrationToken();
        $this->populateUser($user, $selfRegisterData);
        $this->populateClient($client, $selfRegisterData);

        // Check the client is unique
        if (!$this->clientIsUnique($client)) {
            throw new \RuntimeException('User registration: Case number already used', 425);
        }

        // Check the user is unique
        if (!$this->userIsUnique($user)) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.", 422);
        }

        // Check casRec for user
        $criteria = [
            'caseNumber'     => CasRec::normaliseCaseNumber($client->getCaseNumber()),
            'clientLastname' => CasRec::normaliseSurname($client->getLastname()),
            'deputySurname'  => CasRec::normaliseSurname($user->getLastname()),
        ];
        $casRecUserMatches = $this->casRecRepo->findBy($criteria);
        if (count($casRecUserMatches) == 0) {
            throw new \RuntimeException('User registration: not found', 421);
        }

        // Now that multi deputies are a thing, best we can do is ensure that the given postcode matches ONE of the postcodes
        // (Or skip this check completely it if one of the postcodes isn't set)
        $casRecPostcodes = [];
        foreach ($casRecUserMatches as $casRecMatch) {
            if (!empty($casRecMatch->getDeputyPostCode())) {
                $casRecPostcodes[] = $casRecMatch->getDeputyPostCode();
            }
        }
        if (count($casRecPostcodes) == count($casRecUserMatches)) {
            if (!in_array($user->getAddressPostcode(), $casRecPostcodes)){
                throw new \RuntimeException('User registration: postcode mismatch', 424);
            }
        }

        // Currently unable to determine which co-deputy is matched (eg siblings at same address), based on information given
        $deputyNumbers = [];
        foreach ($casRecUserMatches as $casRecMatch) {
            $deputyNumbers[] = $casRecMatch->getDeputyNo();
        }
        $user->setDeputyNo(implode(',', $deputyNumbers));

        // For multi deputy clients
        $casRecCaseMatches = $this->casRecRepo->findBy(['caseNumber' => CasRec::normaliseCaseNumber($client->getCaseNumber())]);
        if (count($casRecCaseMatches) > 1) {
            $user->setCoDeputyClientConfirmed(true);
        }

        $this->saveUserAndClient($user, $client);
        return $user;
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

    public function userIsUnique(User $user)
    {
        return !($user->getEmail() && $this->em->getRepository('AppBundle\Entity\User')->findOneBy(['email' => $user->getEmail()]));
    }
}
