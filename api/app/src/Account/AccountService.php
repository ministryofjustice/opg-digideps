<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Account;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Common\Account\AccountError;
use OPG\Digideps\Common\Account\AccountRequest;
use OPG\Digideps\Common\Account\AccountResponse;
use OPG\Digideps\Common\Account\Area;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Account\ResetRequest;
use OPG\Digideps\Common\Deputy\DeputyType;

final readonly class AccountService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PasswordService $passwordService,
    ) {
    }

    public function requestAccount(AccountRequest $request): AccountResponse|AccountError
    {
        $deputy = $this->findDeputyByRequest($request);
        if ($deputy instanceof AccountError) {
            return $deputy;
        }

        $user = $this->getUserFromDeputy($deputy);
        if ($user->getPassword() !== null) {
            return $user->getEmail() === strtolower($request->deputyEmail->value)
                ? AccountError::PasswordSet : AccountError::AccountTaken;
        }

        // confirm email isn't already in use by a different user (possibly with a different deputy UID)
        $existingUserWithEmail = $this->getUserFromEmail($request->deputyEmail);
        if ($existingUserWithEmail instanceof User) {
            return AccountError::AccountTaken;
        }

        $user->setEmail($request->deputyEmail->value);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->requestPasswordResetForUser($user);
    }

    public function requestPasswordReset(ResetRequest $request): AccountResponse|AccountError
    {
        $user = $this->getUserFromEmail($request->userEmail);
        if ($user instanceof AccountError) {
            return $user;
        }
        return $this->requestPasswordResetForUser($user);
    }

    private function requestPasswordResetForUser(User $user): AccountResponse
    {
        return new AccountResponse(
            $this->passwordService->getTokenFromUser($user),
            $this->passwordService->getValidationKeyFromUser($user),
            new Email($user->getEmail()),
            $user->hasAdminRole() ? Area::Admin : Area::Deputy,
            $user->deriveDeputyType() ?? DeputyType::LAY
        );
    }

    private function getUserFromDeputy(Deputy $deputy): User
    {
        $user = $deputy->getUser();
        if ($user !== null) {
            return $user;
        }

        $user = new User(
            $deputy->getFirstname(),
            $deputy->getLastname(),
            $deputy->getEmail1(),
        );
        $user->setRoleName(match ($deputy->getDeputyType()) {
            DeputyType::LAY => User::ROLE_LAY_DEPUTY,
            DeputyType::PRO => User::ROLE_PROF_NAMED,
            DeputyType::PA => User::ROLE_PA_NAMED,
        });
        $user->setActive(true);
        $user->setDeputyUid($deputy->getDeputyUid());
        $deputy->setUser($user);
        $user->setDeputy($deputy);

        $user->getOrganisations()->clear();
        if ($deputy->getOrganisation() !== null) {
            $user->getOrganisations()->add($deputy->getOrganisation());
        }
        $user->setAddress1($deputy->getAddress1());
        $user->setAddress2($deputy->getAddress2());
        $user->setAddress3($deputy->getAddress3());
        $user->setAddress4($deputy->getAddress4());
        $user->setAddress5($deputy->getAddress5());
        $user->setAddressPostcode($deputy->getAddressPostcode());
        $user->setPhoneMain($deputy->getPhoneMain());
        $user->setPhoneAlternative($deputy->getPhoneAlternative());

        return $user;
    }

    public function findDeputyByRequest(AccountRequest $request): Deputy|AccountError
    {
        $string = Type::getType('string');
        $deputyIds = $this->entityManager->getConnection()->executeQuery("
            SELECT DISTINCT d.id
            FROM deputy d
            JOIN court_order_deputy cod
                ON cod.deputy_id = d.id
                AND cod.is_active
            JOIN court_order co
                ON cod.court_order_id = co.id
                AND co.status = 'ACTIVE'
            JOIN client c
                ON co.client_id = c.id
            WHERE
                c.case_number = ?
                AND c.lastname = ?
                AND d.firstname = ?
                AND d.lastname = ?
                AND d.address_postcode = ?
        ", [
            strlen($request->caseNumber->value) === 10 ? substr($request->caseNumber->value, 0, 8) : $request->caseNumber->value,
            $request->clientLastName,
            $request->deputyFirstName,
            $request->deputyLastName,
            $request->deputyPostCode,
        ], [
            $string,
            $string,
            $string,
            $string,
            $string
        ])->fetchFirstColumn();

        if (count($deputyIds) === 1) {
            return $this->entityManager->find(Deputy::class, $deputyIds[0]) ?? AccountError::MatchingError;
        } else {
            return AccountError::MatchingError;
        }
    }

    private function getUserFromEmail(Email $email): User|AccountError
    {
        return $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => strtolower($email->value)]) ?? AccountError::UnknownEmail;
    }
}
