<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const DELETE_USER = 'delete-user';
    const EDIT_USER = 'edit-user';
    const ADD_USER = 'add-user';
    const CAN_ADD_USER = 'can-add-user';

    /**
     * Does this voter support the attribute?
     *
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return match ($attribute) {
            self::DELETE_USER, self::EDIT_USER, self::CAN_ADD_USER, self::ADD_USER => true,
            default => false,
        };
    }

    /**
     * Vote on whether to grant attribute permission on subject.
     *
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $loggedInUser = $token->getUser();

        if (!$loggedInUser instanceof User) {
            // the loggedUSer must be logged in; if not, deny access
            return false;
        }
        return match ($attribute) {
            self::CAN_ADD_USER => $this->determineCanAddPermission($loggedInUser),
            self::ADD_USER, self::EDIT_USER => $this->determineAddEditPermission($loggedInUser, $subject),
            self::DELETE_USER => $this->determineDeletePermission($loggedInUser, $subject),
            default => false,
        };
    }

    /**
     * Determine whether logged in user can delete a subject user.
     *
     * Ensure any changes are mirrored in API/Client version of this class.
     *
     * @return bool
     */
    private function determineDeletePermission(User $deletor, User $deletee)
    {
        if (!$deletee instanceof User || $deletor->getId() === $deletee->getId()) {
            return false;
        }
        return match ($deletor->getRoleName()) {
            User::ROLE_PA_NAMED, User::ROLE_PA_ADMIN, User::ROLE_PROF_NAMED, User::ROLE_PROF_ADMIN => $this->paProfNamedAdminDeletePermissions($deletee),
            User::ROLE_ADMIN_MANAGER => $this->adminManagerDeletePermissions($deletee),
            User::ROLE_SUPER_ADMIN => true,
            default => false,
        };
    }

    private function paProfNamedAdminDeletePermissions(User $deletee): bool
    {
        return match ($deletee->getRoleName()) {
            User::ROLE_LAY_DEPUTY, User::ROLE_ADMIN, User::ROLE_ADMIN_MANAGER, User::ROLE_SUPER_ADMIN => false,
            default => true,
        };
    }

    private function adminManagerDeletePermissions(User $deletee): bool
    {
        if ($deletee->isAdminManager() || $deletee->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether logged in user can add or edit a subject user.
     *
     * Ensure any changes are mirrored in API/Client version of this class.
     *
     * @return bool
     */
    private function determineAddEditPermission(User $actor, ?User $subject)
    {
        if (!$subject instanceof User) {
            return false;
        }

        if ($actor->getId() === $subject->getId()) {
            return true;
        }

        switch ($actor->getRoleName()) {
            case User::ROLE_SUPER_ADMIN:
                return true;
            case User::ROLE_ADMIN:
            case User::ROLE_AD:
            case User::ROLE_ADMIN_MANAGER:
                if ($subject->isSuperAdmin() || $subject->isAdminManager()) {
                    return false;
                }

                return true;
            case User::ROLE_PA:
            case User::ROLE_PA_NAMED:
                if (
                    $subject->hasAdminRole() ||
                    $subject->isLayDeputy() ||
                    $subject->isDeputyProf()
                ) {
                    return false;
                }

                return true;
            case User::ROLE_PROF:
            case User::ROLE_PROF_NAMED:
                if (
                    $subject->hasAdminRole() ||
                    $subject->isLayDeputy() ||
                    $subject->isDeputyPa()
                ) {
                    return false;
                }

                return true;
            case User::ROLE_PA_ADMIN:
                if (
                    $subject->hasAdminRole() ||
                    $subject->isLayDeputy() ||
                    $subject->isPaNamedDeputy() ||
                    $subject->isPaTopRole() ||
                    $subject->isDeputyProf()
                ) {
                    return false;
                }

                return true;
            case User::ROLE_PROF_ADMIN:
                if (
                    $subject->hasAdminRole() ||
                    $subject->isLayDeputy() ||
                    $subject->isProfNamedDeputy() ||
                    $subject->isProfTopRole() ||
                    $subject->isDeputyPa()
                ) {
                    return false;
                }

                return true;
            default:
                return false;
        }
    }

    private function determineCanAddPermission(User $actor)
    {
        return match ($actor->getRoleName()) {
            User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_AD, User::ROLE_ADMIN_MANAGER, User::ROLE_PA, User::ROLE_PA_NAMED, User::ROLE_PROF, User::ROLE_PROF_NAMED, User::ROLE_PA_ADMIN, User::ROLE_PROF_ADMIN => true,
            default => false,
        };
    }
}
