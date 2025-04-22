<?php

namespace App\Security;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoter extends Voter
{
    const ADD_DOCUMENT = 'add-note';
    const DELETE_DOCUMENT = 'delete-document';

    /**
     * Does this voter support the attribute?
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return
            self::ADD_DOCUMENT === $attribute ||
            self::DELETE_DOCUMENT === $attribute;
    }

    /**
     * Vote on whether to grant attribute permission on subject.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();

        if (!$loggedInUser instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::ADD_DOCUMENT:
                return
                    $subject instanceof Report &&
                    (
                        $subject->getClient()->hasUser($loggedInUser) ||
                        $subject->getClient()->userBelongsToClientsOrganisation($loggedInUser)
                    );

            case self::DELETE_DOCUMENT:
                return
                    $subject instanceof Document &&
                    $subject->getReport() instanceof Report &&
                    (
                        $subject->getReport()->getClient()->hasUser($loggedInUser) ||
                        $subject->getReport()->getClient()->userBelongsToClientsOrganisation($loggedInUser)
                    );
            default:
                return false;
        }
    }
}
