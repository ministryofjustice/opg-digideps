<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\MOJ\Header;

use OPG\Digideps\Frontend\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class AccountNavigation
{
    private ?User $user;
    public array $texts;

    public function __construct(
        private Security $security,
        private TranslatorInterface $translator,
    ) {
        $user = $this->security->getUser();
        $this->user = $user !== null && $this->isLoggedIn && $user instanceof User ? $user : null;
        $this->texts = [
            'fullname' => $this->user?->getFullName() ?? '',
            'yourDetails' => $this->translator->trans('nav.userAccount', domain: 'layout'),
            'signOut' => $this->translator->trans('signOut', domain: 'common')
        ];
    }

    public bool $isLoggedIn {get => $this->security->isGranted('IS_AUTHENTICATED_FULLY');}
    public string $fullname {get => $this->user?->getFullName() ?? '';}
}
