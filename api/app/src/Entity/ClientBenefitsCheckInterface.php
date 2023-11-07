<?php

declare(strict_types=1);

namespace App\Entity;

interface ClientBenefitsCheckInterface
{
    public const WHEN_CHECKED_I_HAVE_CHECKED = 'haveChecked';
    public const WHEN_CHECKED_IM_CURRENTLY_CHECKING = 'currentlyChecking';
    public const WHEN_CHECKED_IVE_NEVER_CHECKED = 'neverChecked';

    public const OTHER_MONEY_YES = 'yes';
    public const OTHER_MONEY_NO = 'no';
    public const OTHER_MONEY_DONT_KNOW = 'dontKnow';
}
