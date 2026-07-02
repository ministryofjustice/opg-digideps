<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\Deputy\DeputyType;

final readonly class Scenario
{
    public function __construct(
        public CourtOrderDescriptor $courtOrderDescriptor,
        public ?Scenario $previous = null,
    ) {
    }

    public static function newSimpleLayScenario(string $deputyReference = 'lay1', CourtOrderReportType $reportType = CourtOrderReportType::OPG103): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($deputyReference)
        ), $reportType));
    }

    public static function newSimpleProScenario(string $deputyReference = 'pro1', CourtOrderReportType $reportType = CourtOrderReportType::OPG103): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($deputyReference, DeputyType::PRO)
        ), $reportType));
    }

    public static function newSimplePaScenario(string $deputyReference = 'pa1', CourtOrderReportType $reportType = CourtOrderReportType::OPG103): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($deputyReference, DeputyType::PA)
        ), $reportType));
    }

    public static function newSimpleAdminProScenario(string $adminReference = 'admin1', string $deputyReference = 'pro1', CourtOrderReportType $reportType = CourtOrderReportType::OPG103): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($adminReference, DeputyType::PRO, userType: UserType::OrgAdmin),
            new DeputyDescriptor($deputyReference, DeputyType::PRO, hasLogin: false),
        ), $reportType));
    }

    public static function newSimpleAdminPaScenario(string $adminReference = 'admin1', string $deputyReference = 'pa1', CourtOrderReportType $reportType = CourtOrderReportType::OPG103): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($adminReference, DeputyType::PA, userType: UserType::OrgAdmin),
            new DeputyDescriptor($deputyReference, DeputyType::PA, hasLogin: false),
        ), $reportType));
    }

    public static function newSimpleTeamMemberProScenario(string $adminReference = 'team1', string $deputyReference = 'pro1', CourtOrderReportType $reportType = CourtOrderReportType::OPG103): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($adminReference, DeputyType::PRO, userType: UserType::OrgTeamMember),
            new DeputyDescriptor($deputyReference, DeputyType::PRO, hasLogin: false),
        ), $reportType));
    }

    public static function newSimpleTeamMemberPaScenario(string $adminReference = 'team1', string $deputyReference = 'pa1', CourtOrderReportType $reportType = CourtOrderReportType::OPG103): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor($adminReference, DeputyType::PA, userType: UserType::OrgTeamMember),
            new DeputyDescriptor($deputyReference, DeputyType::PA, hasLogin: false),
        ), $reportType));
    }
}
