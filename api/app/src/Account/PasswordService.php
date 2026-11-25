<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Account;

use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Entity\Account\AccountToken;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Utility\Environment;
use OPG\Digideps\Common\Account\AccountError;
use OPG\Digideps\Common\Account\Area;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Account\MaximumTokenAge;
use OPG\Digideps\Common\Account\Password;
use OPG\Digideps\Common\Account\PasswordRequest;
use OPG\Digideps\Common\Account\PasswordResponse;
use OPG\Digideps\Common\Account\Token;
use OPG\Digideps\Common\Account\ValidationKey;
use OPG\Digideps\Common\Deputy\DeputyType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class PasswordService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private BruteForceSentinel $bruteForceSentinel,
        private Environment $environment,
    ) {
    }

    public function setPasswordFromRequest(PasswordRequest $request): PasswordResponse|AccountError
    {
        if (!$this->bruteForceSentinel->checkAndRegisterAttempt($request->validationKey)) {
            return AccountError::TokenInvalid;
        }

        $user = $this->getUserFromToken($request->token);
        if ($user instanceof AccountError) {
            return $user;
        }

        if (!hash_equals($this->getValidationKeyFromUser($user)->value, $request->validationKey->value)) {
            return AccountError::TokenInvalid;
        }
        $this->setUserPassword($user, $request->password, true);
        return new PasswordResponse(
            new Email($user->getEmail()),
            $user->hasAdminRole() ? Area::Admin : Area::Deputy,
            $user->deriveDeputyType() ?? DeputyType::LAY
        );
    }

    public function setUserPassword(User $user, Password $password, bool $flush = false): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $password->value));
        if ($user->getRegistrationDate() === null) {
            $user->setRegistrationDate(new \DateTime());
        }
        $user->setActive(true);
        $this->entityManager->persist($user);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    private function getUserFromToken(Token $token): AccountError|User
    {
        if ($token->isExpired(MaximumTokenAge::PasswordReset)) {
            return AccountError::TokenInvalid;
        }
        $accountToken = $this->entityManager->getRepository(AccountToken::class)->findOneBy(['uuid' => $token->uuid]);
        if ($accountToken === null) {
            return AccountError::TokenInvalid;
        }
        return $this->entityManager->find(User::class, $accountToken->userId) ?? AccountError::TokenInvalid;
    }

    public function getTokenFromUser(User $user): Token
    {
        $accountToken = $this->entityManager->find(AccountToken::class, $user->getId());
        if ($accountToken === null) {
            $accountToken = new AccountToken($user->getId());
        } else {
            $accountToken->regenerate();
        }
        $this->entityManager->persist($accountToken);
        $this->entityManager->flush();

        return $accountToken->token;
    }

    public function getValidationKeyFromUser(User $user): ValidationKey
    {
        return new ValidationKey(hash('tiger128,4', $user->getEmail() . $this->environment->validationKeyPepper));
    }
}
