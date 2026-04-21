<?php

declare(strict_types=1);

namespace App\Entity;

interface ClientBenefitsCheckInterface
{
    public const string WHEN_CHECKED_I_HAVE_CHECKED = 'haveChecked';
    public const string WHEN_CHECKED_IM_CURRENTLY_CHECKING = 'currentlyChecking';
    public const string WHEN_CHECKED_IVE_NEVER_CHECKED = 'neverChecked';

    public const string OTHER_MONEY_YES = 'yes';
    public const string OTHER_MONEY_NO = 'no';
    public const string OTHER_MONEY_DONT_KNOW = 'dontKnow';
}
