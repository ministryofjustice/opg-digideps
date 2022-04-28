<?php

namespace App\Controller;

use App\Model\SelfRegisterData;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use App\Service\UserRegistrationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/selfregister")
 */
class SelfRegisterController extends RestController
{
    private LoggerInterface $logger;
    private ValidatorInterface $validator;
    private AuthService $authService;
    private RestFormatter $formatter;

    public function __construct(
        LoggerInterface $logger,
        ValidatorInterface $validator,
        AuthService $authService,
        RestFormatter $formatter
    ) {
        $this->logger = $logger;
        $this->validator = $validator;
        $this->authService = $authService;
        $this->formatter = $formatter;
    }

    /**
     * @Route("", methods={"POST"})
     */
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

    /**
     * @Route("/verifycodeputy", methods={"POST"})
     */
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
            $this->logger->warning('PreRegistration codeputy validation success: ', ['extra' => ['page' => 'codep_validation', 'success' => true] + $selfRegisterData->toArray()]);
        } catch (\Throwable $e) {
            $this->logger->warning('PreRegistration codeputy validation failed:', ['extra' => ['page' => 'codep_validation', 'success' => false] + $selfRegisterData->toArray()]);
            throw $e;
        }

        return ['verified' => $coDeputyVerified];
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
