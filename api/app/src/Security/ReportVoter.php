<?php

namespace OPG\Digideps\Backend\Security;

use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\CourtOrder;
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
    public const string ACCESS = 'access';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute) && is_object($subject) && $this->supportsType(get_class($subject));
    }

    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === self::ACCESS;
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, Report::class, true);
    }

    /**
     * @param Report $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!($user instanceof User)) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return $this->supportsAttribute($attribute) && $this->canAccess($subject, $user);
    }

    private function canAccess(Report $report, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            //OPG Admin; allow access.
            return true;
        }

        //One court order for Single and Dual. Two for Hybrid.
        return array_all($report->getActiveCourtOrders(), fn($courtOrder) => $this->canAccessCourtOrder($courtOrder, $user));
    }

    private function canAccessCourtOrder(CourtOrder $courtOrder, User $user): bool
    {
        foreach ($courtOrder->getActiveDeputies() as $deputy) {
            if ($this->userIsDeputy($user, $deputy)) {
                //User is a deputy directly on the court order.
                return true;
            } elseif ($this->userIsInOrganisation($user, $deputy->getOrganisation())) {
                //User is not Lay and in the same organisation as the deputy.
                return true;
            }
        }
        return false;
    }

    private function userIsDeputy(User $user, Deputy $deputy): bool
    {
        $deputyUid = ($user->getDeputyUid() ?? $user->getDeputy()?->getDeputyUid());
        return $deputy->getDeputyUid() === "{$deputyUid}";
    }

    private function userIsInOrganisation(User $user, ?Organisation $organisation): bool
    {
        //Ideally $user->getDeputy()->getDeputyType() !== DeputyType::LAY but that might be to flaky still;
        return !$user->isLayDeputy() && $organisation !== null && in_array($organisation->getId(), $user->getOrganisationIds());
    }
}
