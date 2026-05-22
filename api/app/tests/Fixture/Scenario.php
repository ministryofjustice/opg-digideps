<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Backend\Domain\Deputy\DeputyType;

final readonly class Scenario
{
    public function __construct(
        public CourtOrderDescriptor $courtOrderDescriptor,
        public ?Scenario $previous = null,
    ) {
    }

    public static function newSimpleLayScenario(string $deputyReference = 'lay1'): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($deputyReference)
        )));
    }

    public static function newSimpleProScenario(string $deputyReference = 'pro1'): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($deputyReference, DeputyType::PRO)
        )));
    }

    public static function newSimplePaScenario(string $deputyReference = 'pa1'): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($deputyReference, DeputyType::PA)
        )));
    }

    public static function newSimpleAdminProScenario(string $adminReference = 'admin1', string $deputyReference = 'pro1'): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($adminReference, DeputyType::PRO, isAdmin: true),
            new DeputyDescriptor($deputyReference, DeputyType::PRO, hasLogin: false),
        )));
    }

    public static function newSimpleAdminPaScenario(string $adminReference = 'admin1', string $deputyReference = 'pa1'): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($adminReference, DeputyType::PA, isAdmin: true),
            new DeputyDescriptor($deputyReference, DeputyType::PA, hasLogin: false),
        )));
    }
}
