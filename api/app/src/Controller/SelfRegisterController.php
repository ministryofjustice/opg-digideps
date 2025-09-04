<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Model\SelfRegisterData;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use App\Service\UserRegistrationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

#[Route(path: '/selfregister')]
class SelfRegisterController extends RestController
{
    public function __construct(private readonly LoggerInterface $logger, private readonly ValidatorInterface $validator, private readonly AuthService $authService, private readonly RestFormatter $formatter, private readonly EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    #[Route(path: '', methods: ['POST'])]
    public function register(Request $request, UserRegistrationService $userRegistrationService): User
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new RuntimeException('client secret not accepted.', 403);
        }

        $selfRegisterData = new SelfRegisterData();

        $this->hydrateEntityWithArrayData($selfRegisterData, $this->formatter->deserializeBodyContent($request), [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail',
            'postcode' => 'setPostcode',
            'client_firstname' => 'setClientFirstname',
            'client_lastname' => 'setClientLastname',
            'case_number' => 'setCaseNumber',
        ]);

        $selfRegisterData->replaceUnicodeChars();

        $caseNumber = $selfRegisterData->getCaseNumber();

        // truncate case number if length is 10
        if (!is_null($caseNumber) && 10 == strlen($caseNumber)) {
            $selfRegisterData->setCaseNumber(substr($caseNumber, 0, -2));
        }

        $errors = $this->validator->validate($selfRegisterData, null, 'self_registration');

        if (count($errors) > 0) {
            throw new RuntimeException('Invalid registration data: '.$errors);
        }

        try {
            $user = $userRegistrationService->selfRegisterUser($selfRegisterData);
            $this->logger->warning('PreRegistration register success: ', ['extra' => ['page' => 'user_registration', 'success' => true] + $selfRegisterData->toArray()]);
        } catch (Throwable $e) {
            $this->logger->warning('PreRegistration register failed:', ['extra' => ['page' => 'user_registration', 'success' => false] + $selfRegisterData->toArray()]);
            throw $e;
        }

        $this->formatter->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    #[Route(path: '/verifycodeputy', methods: ['POST'])]
    public function verifyCoDeputy(Request $request, UserRegistrationService $userRegistrationService): array
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new RuntimeException('client secret not accepted.', 403);
        }

        $selfRegisterData = new SelfRegisterData();

        $this->hydrateEntityWithArrayData($selfRegisterData, $this->formatter->deserializeBodyContent($request), [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail',
            'postcode' => 'setPostcode',
            'client_firstname' => 'setClientFirstname',
            'client_lastname' => 'setClientLastname',
            'case_number' => 'setCaseNumber',
        ]);

        $errors = $this->validator->validate($selfRegisterData, null, ['verify_codeputy']);

        if (count($errors) > 0) {
            throw new RuntimeException('Invalid registration data: '.$errors);
        }

        try {
            $matchedCodeputies = $userRegistrationService->validateCoDeputy($selfRegisterData);

            if (1 !== count($matchedCodeputies)) {
                // a deputy could not be uniquely identified due to matching first name, last name and postcode across more than one deputy record
                $message = sprintf('A unique deputy record for case number %s could not be identified', $selfRegisterData->getCaseNumber());
                throw new RuntimeException(json_encode($message) ?: '', 462);
            }

            // find existing users for this deputy UID, but only those who are not the registering user;
            // we are safe to use the first matched co-deputy, as we checked above that there's only one
            $coDeputyUid = $matchedCodeputies[0]->getDeputyUid();

            /** @var UserRepository $userRepo */
            $userRepo = $this->em->getRepository(User::class);

            $existingDeputyAccounts = $userRepo->findOtherAccounts($coDeputyUid, $selfRegisterData->getEmail());

            // find whether any of the existing users (excluding the registering user) for this deputy UID are already
            // associated with the case
            $existingDeputyCases = $this->em->getRepository(Client::class)
                ->findExistingDeputyCases($selfRegisterData->getCaseNumber(), $coDeputyUid, $selfRegisterData->getEmail());

            if (!empty($existingDeputyCases)) {
                $message = sprintf('A deputy with deputy number %s is already associated with the case number %s', $coDeputyUid, $selfRegisterData->getCaseNumber());
                throw new RuntimeException(json_encode($message) ?: '', 463);
            }

            $this->logger->warning('PreRegistration codeputy validation success: ', ['extra' => ['page' => 'codep_validation', 'success' => true] + $selfRegisterData->toArray()]);
        } catch (Throwable $e) {
            $this->logger->warning('PreRegistration codeputy validation failed:', ['extra' => ['page' => 'codep_validation', 'success' => false] + $selfRegisterData->toArray()]);
            throw $e;
        }

        $this->formatter->setJmsSerialiserGroups(['user', 'verify-codeputy']);

        return ['verified' => true, 'coDeputyUid' => $coDeputyUid, 'existingDeputyAccounts' => $existingDeputyAccounts];
    }

    #[Route(path: '/updatecodeputy/{userId}', requirements: ['userId' => '\d+'], methods: ['PUT'])]
    public function updateCoDeputyWithVerificationData(Request $request, int $userId): User
    {
        $user = $this->em->getRepository('App\Entity\User')->findOneBy(['id' => $userId]);

        $coDeputyVerificationData = $this->formatter->deserializeBodyContent($request);

        $user->setCoDeputyClientConfirmed(true);

        $user->setDeputyNo($coDeputyVerificationData['coDeputyUid']);
        $user->setDeputyUid($coDeputyVerificationData['coDeputyUid']);

        $user->setActive(true);
        $user->setRegistrationDate(new DateTime());
        $user->setPreRegisterValidatedDate(new DateTime());

        if (!$coDeputyVerificationData['existingDeputyAccounts']) {
            $user->setIsPrimary(true);
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->formatter->setJmsSerialiserGroups(['user', 'verify-codeputy']);

        return $user;
    }

    public function populateSelfReg(SelfRegisterData $selfRegisterData, array $data): void
    {
        $this->hydrateEntityWithArrayData($selfRegisterData, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail',
            'postcode' => 'setPostcode',
            'client_firstname' => 'setClientFirstname',
            'client_lastname' => 'setClientLastname',
            'case_number' => 'setCaseNumber',
        ]);
    }
}
