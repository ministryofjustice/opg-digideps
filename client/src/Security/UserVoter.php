<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
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
     * @param  string $attribute
     * @param  mixed  $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        switch ($attribute) {
            case self::DELETE_USER:
            case self::EDIT_USER:
            case self::CAN_ADD_USER:
            case self::ADD_USER:
                return true;
        }

        return false;
    }

    /**
     * Vote on whether to grant attribute permission on subject
     *
     * @param  string         $attribute
     * @param  mixed          $subject
     * @param  TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $loggedInUser= $token->getUser();

        if (!$loggedInUser instanceof User) {
            // the loggedUSer must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::CAN_ADD_USER:
                return $this->determineCanAddPermission($loggedInUser);
            case self::ADD_USER:
            case self::EDIT_USER:
                return $this->determineAddEditPermission($loggedInUser, $subject);
            case self::DELETE_USER:
                return $this->determineDeletePermission($loggedInUser, $subject);
        }

        return false;
    }

    /**
     * Determine whether logged in user can delete a subject user.
     *
     * Ensure any changes are mirrored in API/Client version of this class.
     *
     * @param User $deletor
     * @param User $deletee
     * @return bool
     */
    private function determineDeletePermission(User $deletor, User $deletee)
    {
        if (!$deletee instanceof User || $deletor->getId() === $deletee->getId()) {
            return false;
        }

        switch ($deletor->getRoleName()) {
            case User::ROLE_PA_NAMED:
            case User::ROLE_PA_ADMIN:
            case User::ROLE_PROF_NAMED:
            case User::ROLE_PROF_ADMIN:
                return $this->paProfNamedAdminDeletePermissions($deletee);
            case User::ROLE_ELEVATED_ADMIN:
                return $this->elevatedAdminDeletePermissions($deletee);
            case User::ROLE_SUPER_ADMIN:
                return true;
        }

        return false;
    }

    /**
     * @param User $deletee
     * @return bool
     */
    private function paProfNamedAdminDeletePermissions(User $deletee): bool
    {
        switch ($deletee->getRoleName()) {
            case User::ROLE_LAY_DEPUTY:
            case User::ROLE_ADMIN:
            case User::ROLE_ELEVATED_ADMIN:
            case User::ROLE_SUPER_ADMIN:
                return false;
        }

        return true;
    }

    /**
     * @param User $deletee
     * @return bool
     */
    private function elevatedAdminDeletePermissions(User $deletee): bool
    {
        if ($deletee->isElevatedAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether logged in user can add or edit a subject user.
     *
     * Ensure any changes are mirrored in API/Client version of this class.
     *
     * @param User $actor
     * @param User|null $subject
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
            case User::ROLE_ELEVATED_ADMIN:
                if ($subject->isSuperAdmin() || $subject->isElevatedAdmin()) {
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
        switch ($actor->getRoleName()) {
            case User::ROLE_SUPER_ADMIN:
            case User::ROLE_ADMIN:
            case User::ROLE_AD:
            case User::ROLE_ELEVATED_ADMIN:
            case User::ROLE_PA:
            case User::ROLE_PA_NAMED:
            case User::ROLE_PROF:
            case User::ROLE_PROF_NAMED:
            case User::ROLE_PA_ADMIN:
            case User::ROLE_PROF_ADMIN:
                return true;
            default:
                return false;
        }
    }
}
