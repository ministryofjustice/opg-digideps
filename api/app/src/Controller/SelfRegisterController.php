<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\SelfRegisterData;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use App\Service\UserRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/selfregister')]
class SelfRegisterController extends RestController
{
    public function __construct(private readonly LoggerInterface $logger, private readonly ValidatorInterface $validator, private readonly AuthService $authService, private readonly RestFormatter $formatter, private readonly EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    #[Route(path: '', methods: ['POST'])]
    public function register(Request $request, UserRegistrationService $userRegistrationService)
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
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
            throw new \RuntimeException('Invalid registration data: '.$errors);
        }

        try {
            $user = $userRegistrationService->selfRegisterUser($selfRegisterData);
            $this->logger->warning('PreRegistration register success: ', ['extra' => ['page' => 'user_registration', 'success' => true] + $selfRegisterData->toArray()]);
        } catch (\Throwable $e) {
            $this->logger->warning('PreRegistration register failed:', ['extra' => ['page' => 'user_registration', 'success' => false] + $selfRegisterData->toArray()]);
            throw $e;
        }

        $this->formatter->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    #[Route(path: '/verifycodeputy', methods: ['POST'])]
    public function verifyCoDeputy(Request $request, UserRegistrationService $userRegistrationService)
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
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
            throw new \RuntimeException('Invalid registration data: '.$errors);
        }

        try {
            $coDeputyVerified = $userRegistrationService->validateCoDeputy($selfRegisterData);
            $coDeputyUid = $userRegistrationService->retrieveCoDeputyUid();

            // check if it's the primary account for the co-deputy
            $existingDeputyAccounts = $this->em->getRepository('App\Entity\User')->findBy(['deputyUid' => $coDeputyUid]);

            $existingDeputyCase = $this->em->getRepository('App\Entity\Client')->findExistingDeputyCases($selfRegisterData->getCaseNumber(), $coDeputyUid);
            if (!empty($existingDeputyCase)) {
                throw new \RuntimeException(json_encode(sprintf('A deputy with deputy number %s is already associated with the case number %s', $coDeputyUid, $selfRegisterData->getCaseNumber())), 463);
            }

            $this->logger->warning('PreRegistration codeputy validation success: ', ['extra' => ['page' => 'codep_validation', 'success' => true] + $selfRegisterData->toArray()]);
        } catch (\Throwable $e) {
            $this->logger->warning('PreRegistration codeputy validation failed:', ['extra' => ['page' => 'codep_validation', 'success' => false] + $selfRegisterData->toArray()]);
            throw $e;
        }

        $this->formatter->setJmsSerialiserGroups(['user', 'verify-codeputy']);

        return ['verified' => $coDeputyVerified, 'coDeputyUid' => $coDeputyUid, 'existingDeputyAccounts' => $existingDeputyAccounts];
    }

    #[Route(path: '/updatecodeputy/{userId}', requirements: ['userId' => '\d+'], methods: ['PUT'])]
    public function updateCoDeputyWithVerificationData(Request $request, $userId): User
    {
        $user = $this->em->getRepository('App\Entity\User')->findOneBy(['id' => $userId]);

        $coDeputyVerificationData = $this->formatter->deserializeBodyContent($request);

        $user->setCoDeputyClientConfirmed(true);

        $user->setDeputyNo($coDeputyVerificationData['coDeputyUid']);
        $user->setDeputyUid($coDeputyVerificationData['coDeputyUid']);

        $user->setActive(true);
        $user->setRegistrationDate(new \DateTime());
        $user->setPreRegisterValidatedDate(new \DateTime());

        if (!$coDeputyVerificationData['existingDeputyAccounts']) {
            $user->setIsPrimary(true);
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->formatter->setJmsSerialiserGroups(['user', 'verify-codeputy']);

        return $user;
    }

    public function populateSelfReg(SelfRegisterData $selfRegisterData, array $data)
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
