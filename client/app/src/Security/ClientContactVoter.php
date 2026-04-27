<?php

namespace OPG\Digideps\Frontend\Security;

use OPG\Digideps\Frontend\Entity\Client as ClientEntity;
use OPG\Digideps\Frontend\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, ClientEntity>
 */
class ClientContactVoter extends Voter
{
    public const string ADD_CLIENT_CONTACT = 'add-client-contact';
    public const string EDIT_CLIENT_CONTACT = 'edit-client-contact';
    public const string DELETE_CLIENT_CONTACT = 'delete-client-contact';

    /**
     * Does this voter support the attribute?
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        switch ($attribute) {
            case self::ADD_CLIENT_CONTACT:
            case self::DELETE_CLIENT_CONTACT:
                return true;
            case self::EDIT_CLIENT_CONTACT:
                if ($subject instanceof ClientEntity) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Vote on whether to grant attribute permission on subject.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();

        if (!$loggedInUser instanceof User && $loggedInUser->isPaDeputy()) {
            // the loggedUser must be logged in PA user; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::DELETE_CLIENT_CONTACT:
            case self::EDIT_CLIENT_CONTACT:
            case self::ADD_CLIENT_CONTACT:
                if ($subject instanceof ClientEntity) {
                    return $subject->hasUser($loggedInUser);
                }

                return false;
        }

        return false;
    }
}
