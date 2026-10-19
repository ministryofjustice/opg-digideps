<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Common\Deputy\DeputyType;

final readonly class DeputySet
{
    /**
     * @var array<DeputyDescriptor>
     */
    public array $descriptors;

    public function __construct(DeputyDescriptor ...$descriptors)
    {
        $this->descriptors = $descriptors;
    }

    public static function oneLay(string $deputyReference = 'lay1'): DeputySet
    {
        return new DeputySet(new DeputyDescriptor($deputyReference, DeputyType::LAY));
    }

    public static function manyLay(string ...$deputyReferences): DeputySet
    {
        return new DeputySet(...array_map(fn (string $deputyReference) => new DeputyDescriptor($deputyReference, DeputyType::LAY), $deputyReferences));
    }

    public static function oneNamedPro(string $deputyReference = 'pro1'): DeputySet
    {
        return new DeputySet(new DeputyDescriptor($deputyReference, DeputyType::PRO));
    }

    public static function oneNamedPa(string $deputyReference = 'pa1'): DeputySet
    {
        return new DeputySet(new DeputyDescriptor($deputyReference, DeputyType::PA));
    }

    public static function oneTeam(DeputyType $type, string $prefix, int $teamAdmins, int $teamMembers, bool $namedHasLogin = false): DeputySet
    {
        if ($type === DeputyType::LAY) {
            throw new \DomainException("There are no lay teams");
        }
        $descriptors = [new DeputyDescriptor("{$prefix}-named", $type, hasLogin: $namedHasLogin)];
        for ($i = 1; $i <= $teamAdmins; ++$i) {
            $descriptors[] = new DeputyDescriptor("{$prefix}-admin-{$i}", $type, UserType::OrgAdmin);
        }
        for ($i = 1; $i <= $teamMembers; ++$i) {
            $descriptors[] = new DeputyDescriptor("{$prefix}-member-{$i}", $type, UserType::OrgTeamMember);
        }
        return new DeputySet(...$descriptors);
    }
}
