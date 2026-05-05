<?php

namespace OPG\Digideps\Backend\Security;

use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * @extends Voter<string, Report>
 */
class ReportVoter extends Voter
{
    /** @var string */
    public const string VIEW = 'view';

    /** @var string */
    public const string EDIT = 'edit';

    /** @var string */
    public const string DELETE = 'delete';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]) && $subject instanceof Report;
    }

    /**
     * @param Report $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            self::VIEW, self::EDIT => $this->canAccess($subject, $user),
            self::DELETE => false,
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canAccess(Report $report, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        foreach ($report->getActiveCourtOrders() as $courtOrder) {
            foreach ($courtOrder->getActiveDeputies() as $deputy) {
                if ($this->userIsDeputy($user, $deputy)) {
                    return true;
                } elseif ($this->userIsInOrganisation($user, $deputy->getOrganisation())) {
                    return true;
                }
            }
        }

        return false;
    }

    private function userIsDeputy(User $user, Deputy $deputy): bool
    {
        $deputyUid = ($user->getDeputyUid() ?? $user->getDeputy()?->getDeputyUid());
        return "{$deputy->getDeputyUid()}" === "{$deputyUid}";
    }

    private function userIsInOrganisation(User $user, ?Organisation $organisation): bool
    {
        return !$user->isLayDeputy() && $organisation !== null && in_array($organisation->getId(), $user->getOrganisationIds());
    }
}
