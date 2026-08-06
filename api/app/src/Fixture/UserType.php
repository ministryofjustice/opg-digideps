<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Backend\Entity\User;

enum UserType: string
{
    case Deputy = User::ROLE_DEPUTY;
    case OrgAdmin = User::ROLE_ORG_ADMIN;
    case OrgTeamMember = User::ROLE_ORG_TEAM_MEMBER;
    case Admin = User::ROLE_ADMIN;
    case AdminManager = User::ROLE_ADMIN_MANAGER;
    case SuperAdmin = User::ROLE_SUPER_ADMIN;
    case PaNamedUser = User::ROLE_PA_NAMED;
    case ProNamedUser = User::ROLE_PROF_NAMED;
}
