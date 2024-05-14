<?php

namespace App\Security;

use App\Entity\Client;
use App\Entity\Note;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NoteVoter extends Voter
{
    public const ADD_NOTE = 'add-note';
    public const EDIT_NOTE = 'edit-note';
    public const DELETE_NOTE = 'delete-note';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * NoteVoter constructor.
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * Does this voter support the attribute?
     *
     * @param string $attribute
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        switch ($attribute) {
            case self::ADD_NOTE:
            case self::DELETE_NOTE:
                return true;
            case self::EDIT_NOTE:
                // only vote on User objects inside this voter
                if (self::EDIT_NOTE === $attribute && $subject instanceof Note) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Vote on whether to grant attribute permission on subject.
     *
     * @param string $attribute
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $token->getUser();

        if (!$loggedInUser instanceof User && $loggedInUser->isPaDeputyAnyRole()) {
            // the loggedUser must be logged in PA user; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::ADD_NOTE:
                if ($subject instanceof Client) {
                    /* @var Client $subject */
                    return $subject->hasUser($loggedInUser);
                }

                return false;
            case self::EDIT_NOTE:
            case self::DELETE_NOTE:
                if ($subject instanceof Note) {
                    $client = $subject->getClient();
                    if ($client instanceof Client) {
                        /* @var Note $subject */
                        return $subject->getClient()->hasUser($loggedInUser);
                    }
                }

                return false;
        }

        return false;
    }
}
