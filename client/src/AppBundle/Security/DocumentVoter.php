<?php

namespace AppBundle\Security;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoter extends Voter
{
    const ADD_DOCUMENT = 'add-note';
    const DELETE_DOCUMENT = 'delete-document';

    /**
     * Does this voter support the attribute?
     *
     * @param  string $attribute
     * @param  mixed  $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return
            $attribute === self::ADD_DOCUMENT ||
            $attribute === self::DELETE_DOCUMENT;
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
        /** @var User $loggedInUser */
        $loggedInUser= $token->getUser();

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
