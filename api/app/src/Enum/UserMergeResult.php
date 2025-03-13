<?php

namespace App\Enum;

enum UserMergeResult: string
{
    case MERGED = 'users merged';
    case DEPUTY_UIDS_MISMATCHED = 'users have different deputy UIDs';
    case FROM_USER_NOT_FOUND = 'could not find user with given "from" email';
    case INTO_USER_NOT_FOUND = 'could not find user with given "into" email';
}
