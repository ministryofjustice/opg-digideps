<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Backend\Domain\Deputy\DeputyType;

final readonly class DeputyDescriptor
{
    public string $emailDomain;
    public string $organisation;

    public function __construct(
        public string $deputyReference,
        public DeputyType $type = DeputyType::LAY,
        public UserType $userType = UserType::Deputy,
        public bool $hasLogin = true,
        public bool $isActive = true,
        public bool $isPrimary = true,
        public bool $isLoginActive = true,
        ?string $emailDomain = null,
    ) {
        if (!$this->hasLogin && $this->userType !== UserType::Deputy) {
            throw new \DomainException('Non deputy users must have a login.');
        }

        $base = $emailDomain ?? 'default';
        $type = strtolower($this->type->value);
        $this->emailDomain = "{$base}.{$type}.digideps.test";

        if ($this->type !== DeputyType::LAY) {
            $base[0] = strtoupper($base[0]);
            $type[0] = strtoupper($type[0]);
            $this->organisation = "{$type} {$base}";
        } else {
            $this->organisation = '';
        }
    }
}
