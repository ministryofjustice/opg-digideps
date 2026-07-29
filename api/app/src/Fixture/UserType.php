<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

enum UserType
{
    case Deputy;
    case OrgAdmin;
    case OrgTeamMember;
    case Admin;
    case AdminManager;
    case SuperAdmin;
}
