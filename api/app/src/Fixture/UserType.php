<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

enum UserType: string
{
    case Deputy = 'Deputy';
    case OrgAdmin = 'OrgAdmin';
    case OrgTeamMember = 'OrgTeamMember';
    case Admin = 'Admin';
    case AdminManager = 'AdminManager';
    case SuperAdmin = 'SuperAdmin';
}
